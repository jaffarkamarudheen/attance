<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NetworkLog;
use Illuminate\Support\Facades\Http;

class GeocodeMissingLocations extends Command
{
    protected $signature = 'app:geocode-missing-locations';
    protected $description = 'Fills missing location names in network logs using coordinates';

    public function handle()
    {
        $logs = NetworkLog::whereNull('location_name')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $this->info("Found " . $logs->count() . " logs missing location names.");

        foreach ($logs as $log) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'AttanceWebApp/1.0 (contact@example.com)'
                ])->timeout(5)->get("https://nominatim.openstreetmap.org/reverse?format=json&lat={$log->latitude}&lon={$log->longitude}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['display_name'])) {
                        $log->update(['location_name' => $data['display_name']]);
                        $this->line("Updated log {$log->id} with location: {$data['display_name']}");
                    }
                }
                // Sleep for 1 second to respect Nominatim API limits (max 1 req/sec)
                sleep(1);
            } catch (\Exception $e) {
                $this->error("Failed for log {$log->id}: " . $e->getMessage());
            }
        }

        $this->info("Done!");
    }
}
