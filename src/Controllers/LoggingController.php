<?php

namespace BehinLogging\Controllers;

use App\Http\Controllers\Controller;
use BehinLogging\Models\UserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoggingController extends Controller
{
    public static function info($channel, $log)
    {
        if (!$channel) return;
        Log::channel($channel)->info($log);
    }

    public function index(Request $request)
    {
        $logs = UserActionLog::query()->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))->latest()->paginate(50)->withQueryString();
        return view('BehinLoggingViews::index', compact('logs'));
    }
}
