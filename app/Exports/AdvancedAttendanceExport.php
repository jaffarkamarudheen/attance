<?php

namespace App\Exports;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\NetworkLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdvancedAttendanceExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $userId;
    protected $fromDate;
    protected $toDate;
    protected $reportData = [];

    public function __construct($userId, $fromDate, $toDate)
    {
        $this->userId = $userId;
        $this->fromDate = Carbon::parse($fromDate)->startOfDay();
        $this->toDate = Carbon::parse($toDate)->endOfDay();
        $this->generateReportData();
    }

    protected function generateReportData()
    {
        // Get the relevant users
        $query = User::where('role', 'Employee');
        if ($this->userId !== 'all') {
            $query->where('id', $this->userId);
        }
        $users = $query->get();

        $data = [];

        // Loop through each day in the date range
        for ($date = $this->fromDate->copy(); $date->lte($this->toDate); $date->addDay()) {
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            foreach ($users as $user) {
                // Get all attendance records for this user on this day
                $records = AttendanceRecord::where('user_id', $user->id)
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->orderBy('punch_in', 'asc')
                    ->get();

                if ($records->isEmpty()) {
                    // Skip absent days to keep the report clean as assumed
                    continue;
                }

                $firstPunchIn = $records->first()->punch_in;
                $lastRecord = $records->whereNotNull('punch_out')->last();
                $lastPunchOut = $lastRecord ? $lastRecord->punch_out : null;
                
                $totalHours = '-';
                if ($firstPunchIn && $lastPunchOut) {
                    $totalHours = round($lastPunchOut->diffInMinutes($firstPunchIn) / 60, 2);
                }

                // Get network logs to find WiFi and Location (take all logs of the day)
                $networkLogs = NetworkLog::where('user_id', $user->id)
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->orderBy('created_at', 'asc')
                    ->get();

                $ssid = '-';
                $location = '-';

                if ($networkLogs->isNotEmpty()) {
                    $ssidArray = [];
                    foreach ($networkLogs as $log) {
                        if ($log->ssid) {
                            $ssidArray[] = $log->ssid . ' : ' . $log->created_at->format('h:i A');
                        }
                    }
                    if (count($ssidArray) > 0) {
                        $ssid = implode(', ', $ssidArray);
                    }

                    $firstLogWithLocation = $networkLogs->whereNotNull('latitude')->first();
                    if ($firstLogWithLocation) {
                        if ($firstLogWithLocation->location_name) {
                            $location = $firstLogWithLocation->location_name;
                        } else {
                            $location = $firstLogWithLocation->latitude . ', ' . $firstLogWithLocation->longitude;
                        }
                    }
                }

                $data[] = [
                    'Employee Name' => $user->name,
                    'Date' => $date->format('Y-m-d'),
                    'First Punch In' => $firstPunchIn ? $firstPunchIn->format('h:i A') : 'N/A',
                    'Last Punch Out' => $lastPunchOut ? $lastPunchOut->format('h:i A') : 'Active/No Out',
                    'Total Hours' => $totalHours,
                    'Wi-Fi (SSID)' => $ssid,
                    'Location (GPS)' => $location
                ];
            }
        }

        $this->reportData = $data;
    }

    public function getReportData()
    {
        return $this->reportData;
    }

    public function array(): array
    {
        return $this->reportData;
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Date',
            'First Punch In',
            'Last Punch Out',
            'Total Hours',
            'Wi-Fi (SSID)',
            'Location (GPS)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
