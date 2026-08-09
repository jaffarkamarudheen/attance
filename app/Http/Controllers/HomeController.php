<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'Admin') {
            $employees = \App\Models\User::where('role', 'Employee')->get();
            $attendance = \App\Models\AttendanceRecord::with('user')->latest()->get();
            $networkLogs = \App\Models\NetworkLog::with('user')->latest()->get();
            return view('admin.dashboard', compact('employees', 'attendance', 'networkLogs'));
        }

        $activePunch = \App\Models\AttendanceRecord::where('user_id', $user->id)
            ->whereNull('punch_out')
            ->latest()
            ->first();
            
        $myAttendance = \App\Models\AttendanceRecord::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('employee.dashboard', compact('activePunch', 'myAttendance'));
    }

    public function exportPdf()
    {
        if (auth()->user()->role !== 'Admin') return abort(403);
        $attendance = \App\Models\AttendanceRecord::with('user')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf', compact('attendance'));
        return $pdf->download('attendance_report.pdf');
    }

    public function exportExcel()
    {
        if (auth()->user()->role !== 'Admin') return abort(403);
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AttendanceExport, 'attendance_report.xlsx');
    }

    public function storeEmployee(Request $request)
    {
        if (auth()->user()->role !== 'Admin') return abort(403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'Employee',
        ]);

        return redirect()->back()->with('success', 'Employee account created successfully!');
    }

    public function exportAdvancedReport(Request $request)
    {
        if (auth()->user()->role !== 'Admin') return abort(403);

        $request->validate([
            'user_id' => 'required',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'format' => 'required|in:excel,pdf'
        ]);

        $export = new \App\Exports\AdvancedAttendanceExport(
            $request->user_id,
            $request->from_date,
            $request->to_date
        );

        if ($request->format === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(
                $export, 
                "advanced_attendance_report.xlsx"
            );
        } else {
            // PDF
            $data = $export->getReportData();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.advanced_pdf', compact('data'))
                    ->setPaper('a4', 'landscape');
            return $pdf->download('advanced_attendance_report.pdf');
        }
    }
}
