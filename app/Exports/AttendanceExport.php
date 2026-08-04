<?php

namespace App\Exports;

use App\Models\AttendanceRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return AttendanceRecord::with('user')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Punch In',
            'Punch Out',
            'Duration (Hours)'
        ];
    }

    public function map($record): array
    {
        $duration = '-';
        if ($record->punch_in && $record->punch_out) {
            $duration = round($record->punch_out->diffInMinutes($record->punch_in) / 60, 2);
        }

        return [
            $record->id,
            $record->user->name,
            $record->punch_in ? $record->punch_in->format('Y-m-d H:i:s') : 'N/A',
            $record->punch_out ? $record->punch_out->format('Y-m-d H:i:s') : 'Active',
            $duration,
        ];
    }
}
