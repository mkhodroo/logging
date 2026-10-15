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
        $logs = UserActionLog::query()
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('user_name'), fn($q) => $q->where('user_name', 'LIKE', '%' . $request->user_name . '%'))
            ->when($request->filled('path'), fn($q) => $q->where(function ($qq) use ($request) {
                $qq->where('path', 'LIKE', '%' . $request->path . '%')
                    ->orWhere('action', 'LIKE', '%' . $request->path . '%');
            }))
            ->when($request->filled('method'), fn($q) => $q->where('method', $request->method))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('detail'), function ($q) use ($request) {
                $d = '%' . $request->detail . '%';
                $q->where(function ($qq) use ($d) {
                    $qq->where('action', 'LIKE', $d)
                        ->orWhere('path', 'LIKE', $d)
                        ->orWhere('user_agent', 'LIKE', $d)
                        ->orWhere('params', 'LIKE', $d)
                        ->orWhere('ip_address', 'LIKE', $d);
                });
            })
            ->latest()->paginate(50)->withQueryString();
        return view('BehinLoggingViews::index', compact('logs'));
    }

    public function show($id)
    {
        $log = UserActionLog::findOrFail($id);
        return view('BehinLoggingViews::show', compact('log'));
    }
}
