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

    public function punchIn(Request $request)
    {
        $user = $request->user();
        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'punch_in' => now(),
        ]);

        // Log location immediately on punch in
        NetworkLog::create([
            'user_id' => $user->id,
            'ssid' => $request->ssid ?? 'Web App (Punch In)',
            'local_ip' => $request->local_ip ?? '-',
            'public_ip' => $request->ip(),
            'device_name' => $request->device_name ?? (substr($request->userAgent(), 0, 255) ?? 'Web Browser'),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
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

            // Log location immediately on punch out
            NetworkLog::create([
                'user_id' => $user->id,
                'ssid' => $request->ssid ?? 'Web App (Punch Out)',
                'local_ip' => $request->local_ip ?? '-',
                'public_ip' => $request->ip(),
                'device_name' => $request->device_name ?? (substr($request->userAgent(), 0, 255) ?? 'Web Browser'),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_name' => $request->location_name,
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
        
        $log = NetworkLog::create([
            'user_id' => auth()->id(),
            'ssid' => 'Web App',
            'local_ip' => '-',
            'public_ip' => $request->ip(),
            'device_name' => substr($userAgent, 0, 255) ?? 'Web Browser',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
        ]);

        return response()->json(['message' => 'Web Network logged successfully', 'log' => $log]);
    }
}
