<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::all();
        return response()->json($logs);
    }
}


// Nanti Di lanjutkan kembali
