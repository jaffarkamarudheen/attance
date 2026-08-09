<!DOCTYPE html>
<html>
<head>
    <title>Advanced Attendance Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Advanced Attendance Report</h2>
    <p>Generated on: {{ now()->format('M d, Y H:i:s') }}</p>
    <table>
        <thead>
            <tr>
                <th>Employee Name</th>
                <th>Date</th>
                <th>First Punch In</th>
                <th>Last Punch Out</th>
                <th>Total Hours</th>
                <th>Wi-Fi (SSID)</th>
                <th>Location (GPS)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr>
                <td>{{ $row['Employee Name'] }}</td>
                <td>{{ $row['Date'] }}</td>
                <td>{{ $row['First Punch In'] }}</td>
                <td>{{ $row['Last Punch Out'] }}</td>
                <td>{{ $row['Total Hours'] }}</td>
                <td>{{ $row['Wi-Fi (SSID)'] }}</td>
                <td>{{ $row['Location (GPS)'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">No attendance records found for the selected criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
