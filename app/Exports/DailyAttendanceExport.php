<?php

namespace App\Exports;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\NetworkLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyAttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $date;

    public function __construct($date)
    {
        $this->date = Carbon::parse($date)->startOfDay();
    }

    public function collection()
    {
        // Get all employees
        return User::where('role', 'Employee')->get();
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

    public function map($user): array
    {
        $startOfDay = $this->date->copy()->startOfDay();
        $endOfDay = $this->date->copy()->endOfDay();

        // Get all attendance records for this user on this day
        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('punch_in', 'asc')
            ->get();

        $firstPunchIn = null;
        $lastPunchOut = null;
        $totalHours = '-';

        if ($records->isNotEmpty()) {
            $firstPunchIn = $records->first()->punch_in;
            // Get the last record that has a punch_out, or if none, maybe they are still active
            $lastRecord = $records->whereNotNull('punch_out')->last();
            if ($lastRecord) {
                $lastPunchOut = $lastRecord->punch_out;
            }

            if ($firstPunchIn && $lastPunchOut) {
                $totalHours = round($lastPunchOut->diffInMinutes($firstPunchIn) / 60, 2);
            }
        }

        // Get network logs to find WiFi and Location
        $networkLog = NetworkLog::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('created_at', 'asc')
            ->first();

        $ssid = '-';
        $location = '-';

        if ($networkLog) {
            $ssid = $networkLog->ssid ?? '-';
            if ($networkLog->latitude && $networkLog->longitude) {
                $location = $networkLog->latitude . ', ' . $networkLog->longitude;
            }
        }

        return [
            $user->name,
            $this->date->format('Y-m-d'),
            $firstPunchIn ? $firstPunchIn->format('h:i A') : 'Absent',
            $lastPunchOut ? $lastPunchOut->format('h:i A') : ($firstPunchIn ? 'Active/No Out' : '-'),
            $totalHours,
            $ssid,
            $location
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
