<?php

namespace App\Exports;

use App\Models\NetworkLog;
use Maatwebsite\Excel\Concerns\FromCollection;

class NetworkLogsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return NetworkLog::all();
    }
}
