@extends('layouts.app')

@section('content')
<div class="container-fluid px-5 py-4">
    <div class="row mb-4">
<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-shield-alt text-primary me-2"></i>Admin Control Center</h2>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
            <a href="{{ url('/admin/reports/pdf') }}" class="btn btn-danger rounded-pill shadow-sm btn-hover-scale px-4">
                <i class="fas fa-file-pdf me-2"></i> Export PDF
            </a>
            <a href="{{ url('/admin/reports/excel') }}" class="btn btn-success rounded-pill shadow-sm btn-hover-scale px-4">
                <i class="fas fa-file-excel me-2"></i> Export All
            </a>
            <button class="btn btn-warning text-white rounded-pill shadow-sm btn-hover-scale px-4" data-bs-toggle="modal" data-bs-target="#filterReportModal">
                <i class="fas fa-filter me-2"></i> Filter Report
            </button>
            <button class="btn btn-info text-white rounded-pill shadow-sm btn-hover-scale px-4" data-bs-toggle="modal" data-bs-target="#dailyExportModal">
                <i class="fas fa-calendar-day me-2"></i> Daily Report
            </button>
            <button class="btn btn-primary px-4 fw-medium shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">
                <i class="fas fa-user-plus me-1"></i> New Employee
            </button>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger px-4 fw-medium shadow-sm rounded-pill">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Create Employee Modal -->
    <div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Create Employee Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ url('/admin/employees') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Full Name</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light" required placeholder="John Doe">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-lg bg-light" required placeholder="employee@company.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Password</label>
                            <input type="password" name="password" class="form-control form-control-lg bg-light" required placeholder="Min. 8 characters">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control form-control-lg bg-light" required placeholder="Re-type password">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 fw-medium shadow-sm">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Daily Export Modal -->
    <div class="modal fade" id="dailyExportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-day me-2"></i>Download Daily Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ url('/admin/reports/daily-excel') }}" method="GET">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Select Date</label>
                            <input type="date" name="date" class="form-control form-control-lg bg-light" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                            <small class="text-muted mt-2 d-block">This will export a report showing the first punch in, last punch out, total hours, and location/Wi-Fi for all employees on the selected date.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white px-4 fw-medium shadow-sm">Download Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filter Report Modal -->
    <div class="modal fade" id="filterReportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-filter me-2"></i>Filter Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ url('/admin/reports/advanced') }}" method="GET">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Select Employee</label>
                            <select name="user_id" class="form-select form-control-lg bg-light" required>
                                <option value="all">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-lg bg-light" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-lg bg-light" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Report Type</label>
                            <select name="format" class="form-select form-control-lg bg-light" required>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white px-4 fw-medium shadow-sm">Generate Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="row">
        <!-- Employees Column -->
        <div class="col-lg-4 mb-4">
            <div class="card glass-card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="fw-bold text-secondary mb-0">Employees</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        @forelse($employees as $emp)
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 border-bottom-dashed">
                                <div>
                                    <h6 class="mb-0 fw-semibold">{{ $emp->name }}</h6>
                                    <small class="text-muted">{{ $emp->email }}</small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Employee</span>
                            </li>
                        @empty
                            <li class="list-group-item bg-transparent text-muted text-center py-4 border-0">No employees found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Attendance Column -->
        <div class="col-lg-8 mb-4">
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="fw-bold text-secondary mb-0">Recent Attendance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover align-middle">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Punch In</th>
                                    <th>Punch Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendance as $record)
                                <tr>
                                    <td class="fw-medium">{{ $record->user->name }}</td>
                                    <td>{{ $record->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                            {{ $record->punch_in ? $record->punch_in->format('h:i A') : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($record->punch_out)
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2">
                                            {{ $record->punch_out->format('h:i A') }}
                                        </span>
                                        @else
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-2">Active</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $attendance->appends(request()->except('attendance_page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

            <!-- Network Logs -->
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <h5 class="fw-bold text-secondary mb-0">Network Monitoring History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover align-middle">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Employee</th>
                                    <th>SSID</th>
                                    <th>Local IP</th>
                                    <th>Public IP</th>
                                    <th>Location (GPS)</th>
                                    <th>Logged At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($networkLogs as $log)
                                <tr>
                                    <td class="fw-medium">{{ $log->user->name }}</td>
                                    <td><span class="text-info fw-semibold"><i class="fas fa-wifi me-1"></i> {{ $log->ssid ?? 'Unknown' }}</span></td>
                                    <td><code class="text-muted">{{ $log->local_ip ?? '-' }}</code></td>
                                    <td><code class="text-muted">{{ $log->public_ip ?? '-' }}</code></td>
                                    <td>
                                        @if($log->latitude && $log->longitude)
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank" class="text-info fw-semibold text-decoration-none small" title="View on Map">
                                                <i class="fas fa-map-marker-alt me-1"></i> {{ Str::limit($log->location_name ?? 'View on Map', 30) }}
                                            </a>
                                        @else
                                            <span class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> Location Disabled</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $log->created_at->format('M d, h:i A') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No network logs found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $networkLogs->appends(request()->except('network_page'))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
