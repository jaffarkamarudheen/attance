<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Employee Attendance Report</h2>
    <p>Generated on: {{ now()->format('M d, Y H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Employee Name</th>
                <th>Punch In</th>
                <th>Punch Out</th>
                <th>Duration (hrs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendance as $record)
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ $record->user->name }}</td>
                <td>{{ $record->punch_in ? $record->punch_in->format('Y-m-d H:i:s') : 'N/A' }}</td>
                <td>{{ $record->punch_out ? $record->punch_out->format('Y-m-d H:i:s') : 'Active' }}</td>
                <td>
                    @if($record->punch_in && $record->punch_out)
                        {{ round($record->punch_out->diffInMinutes($record->punch_in) / 60, 2) }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
