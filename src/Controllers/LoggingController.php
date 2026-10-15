<?php

namespace BehinLogging\Controllers;

use App\Http\Controllers\Controller;
use BehinLogging\Models\UserActionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

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
            ->when($request->filled('date_from'), function ($q) use ($request) {
                if ($g = $this->jalaliToGregorian($request->date_from)) {
                    $q->whereDate('created_at', '>=', $g);
                }
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                if ($g = $this->jalaliToGregorian($request->date_to)) {
                    $q->whereDate('created_at', '<=', $g);
                }
            })
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

    public function show($id, Request $request)
    {
        $log = UserActionLog::findOrFail($id);
        $jalaliDate = $log->created_at ? Jalalian::fromCarbon($log->created_at)->format('Y/m/d H:i:s') : '-';
        $paramsPretty = $log->params ? json_encode($log->params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $log->id,
                'user_name' => $log->user_name,
                'user_id' => $log->user_id,
                'method' => $log->method,
                'action' => $log->action,
                'path' => $log->path,
                'status_code' => $log->status_code,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at_jalali' => $jalaliDate,
                'params_pretty' => $paramsPretty,
            ]);
        }

        return view('BehinLoggingViews::show', compact('log', 'jalaliDate', 'paramsPretty'));
    }

    protected function jalaliToGregorian($jalaliDate): ?string
    {
        try {
            $jalaliDate = trim((string) $jalaliDate);
            if ($jalaliDate === '') return null;
            $jalaliDate = str_replace('/', '-', $jalaliDate);
            $parts = explode('-', $jalaliDate);
            if (count($parts) < 3) return null;
            return Jalalian::fromFormat('Y-m-d', sprintf('%04d-%02d-%02d', (int) $parts[0], (int) $parts[1], (int) $parts[2]))
                ->toCarbon()->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
