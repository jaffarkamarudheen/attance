<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\NetworkLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('desktop-agent')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    private function resolveLocationName($lat, $lon, $providedName)
    {
        if (!empty($providedName) && $providedName !== 'Unknown Location') {
            return $providedName;
        }
        
        if ($lat && $lon) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'AttanceWebApp/1.0 (contact@example.com)'
                ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    return $data['display_name'] ?? 'Unknown Location';
                }
            } catch (\Exception $e) {
                // Ignore and fall through
            }
        }
        return null;
    }

    public function punchIn(Request $request)
    {
        $user = $request->user();
        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'punch_in' => now(),
        ]);

        $locationName = $this->resolveLocationName($request->latitude, $request->longitude, $request->location_name);

        // Log location immediately on punch in
        NetworkLog::create([
            'user_id' => $user->id,
            'ssid' => $request->ssid ?? 'Web App (Punch In)',
            'local_ip' => $request->local_ip ?? '-',
            'public_ip' => $request->ip(),
            'device_name' => $request->device_name ?? (substr($request->userAgent(), 0, 255) ?? 'Web Browser'),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $locationName,
        ]);

        return response()->json(['message' => 'Punched in successfully', 'record' => $record]);
    }

    public function punchOut(Request $request)
    {
        $user = $request->user();
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereNull('punch_out')
            ->latest()
            ->first();

        if ($attendance) {
            $attendance->update([
                'punch_out' => now(),
            ]);

            $locationName = $this->resolveLocationName($request->latitude, $request->longitude, $request->location_name);

            // Log location immediately on punch out
            NetworkLog::create([
                'user_id' => $user->id,
                'ssid' => $request->ssid ?? 'Web App (Punch Out)',
                'local_ip' => $request->local_ip ?? '-',
                'public_ip' => $request->ip(),
                'device_name' => $request->device_name ?? (substr($request->userAgent(), 0, 255) ?? 'Web Browser'),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_name' => $locationName,
            ]);

            return response()->json(['message' => 'Punched out successfully', 'attendance' => $attendance]);
        }

        return response()->json(['message' => 'No active punch in found'], 404);
    }

    public function networkLog(Request $request)
    {
        $request->validate([
            'ssid' => 'nullable|string',
            'local_ip' => 'nullable|string',
            'public_ip' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        $log = NetworkLog::create([
            'user_id' => $request->user()->id,
            'ssid' => $request->ssid,
            'local_ip' => $request->local_ip,
            'public_ip' => $request->public_ip,
            'device_name' => $request->device_name,
        ]);

        return response()->json(['message' => 'Network logged successfully', 'log' => $log]);
    }

    public function webNetworkLog(Request $request)
    {
        // Get device name from User-Agent, default to 'Web Browser'
        $userAgent = $request->userAgent();
        
        $locationName = $this->resolveLocationName($request->latitude, $request->longitude, $request->location_name);

        $log = NetworkLog::create([
            'user_id' => auth()->id(),
            'ssid' => 'Web App',
            'local_ip' => '-',
            'public_ip' => $request->ip(),
            'device_name' => substr($userAgent, 0, 255) ?? 'Web Browser',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $locationName,
        ]);

        return response()->json(['message' => 'Web Network logged successfully', 'log' => $log]);
    }
}
