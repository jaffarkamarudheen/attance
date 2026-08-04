@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card glass-card border-0 shadow-lg mb-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                    <h3 class="fw-bold mb-0 text-primary-gradient">Employee Dashboard</h3>
                    <p class="text-muted small">Welcome back, {{ auth()->user()->name }}</p>
                </div>

                <div class="card-body text-center py-5">
                    
                    @if($activePunch)
                        <h4 class="text-success mb-4">You are currently Punched In</h4>
                        <p class="text-muted mb-4">Punched in at: {{ $activePunch->punch_in->format('h:i A - M d, Y') }}</p>
                        
                        <form action="{{ url('/punch-out') }}" method="POST" id="punchOutForm">
                            @csrf
                            <button type="button" class="btn btn-danger btn-lg rounded-pill px-5 shadow-sm btn-hover-scale" onclick="handlePunch('punchOutForm')">
                                <i class="fas fa-sign-out-alt me-2"></i> Punch Out
                            </button>
                        </form>
                    @else
                        <h4 class="text-primary mb-4">Ready to start your shift?</h4>
                        <form action="{{ url('/punch-in') }}" method="POST" id="punchInForm">
                            @csrf
                            <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm btn-hover-scale" onclick="handlePunch('punchInForm')">
                                <i class="fas fa-sign-in-alt me-2"></i> Punch In
                            </button>
                        </form>
                    @endif

                </div>
            </div>

            <div class="card glass-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h5 class="fw-bold text-secondary">Recent Attendance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover align-middle">
                            <thead class="text-muted small text-uppercase">
                                <tr>
                                    <th>Date</th>
                                    <th>Punch In</th>
                                    <th>Punch Out</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myAttendance->take(5) as $record)
                                <tr>
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
                                    <td colspan="3" class="text-center text-muted py-4">No recent records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Explicit Logout Button for convenience -->
            <div class="text-center mt-4">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="btn btn-outline-secondary rounded-pill px-4 shadow-sm btn-hover-scale">
                    <i class="fas fa-sign-out-alt me-2"></i> Log Out
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function submitPunchForm(form, lat, lon, locName) {
        let formData = new FormData(form);
        if (lat) formData.append('latitude', lat);
        if (lon) formData.append('longitude', lon);
        if (locName) formData.append('location_name', locName);
        
        axios.post(form.action, formData)
            .then(response => {
                window.location.reload();
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
                console.error(error);
            });
    }

    function handlePunch(formId) {
        let form = document.getElementById(formId);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                let lat = position.coords.latitude;
                let lon = position.coords.longitude;
                
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                    .then(response => response.json())
                    .then(data => {
                        let locationName = data.display_name || 'Unknown Location';
                        submitPunchForm(form, lat, lon, locationName);
                    })
                    .catch(error => submitPunchForm(form, lat, lon, null));
            }, (error) => {
                console.warn('Geolocation failed or denied.', error);
                submitPunchForm(form, null, null, null);
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        } else {
            submitPunchForm(form, null, null, null);
        }
    }

</script>
@endsection
