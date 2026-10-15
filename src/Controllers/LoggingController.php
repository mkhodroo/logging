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
        // NOTE: all filters below are combined with AND.
        // OR is only used *inside* a single box (e.g. path matches path OR action),
        // wrapped in a grouped where() so it never leaks into the other filters.
        $query = UserActionLog::query();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('user_name')) {
            $query->where('user_name', 'LIKE', '%' . $request->user_name . '%');
        }

        if ($request->filled('path')) {
            $path = '%' . $request->path . '%';
            $query->where(function ($qq) use ($path) {
                $qq->where('path', 'LIKE', $path)
                    ->orWhere('action', 'LIKE', $path);
            });
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('date_from')) {
            if ($g = $this->jalaliToGregorian($request->date_from)) {
                $query->whereDate('created_at', '>=', $g);
            }
        }

        if ($request->filled('date_to')) {
            if ($g = $this->jalaliToGregorian($request->date_to)) {
                $query->whereDate('created_at', '<=', $g);
            }
        }

        if ($request->filled('detail')) {
            $d = '%' . $request->detail . '%';
            $query->where(function ($qq) use ($d) {
                $qq->where('action', 'LIKE', $d)
                    ->orWhere('path', 'LIKE', $d)
                    ->orWhere('user_agent', 'LIKE', $d)
                    ->orWhere('params', 'LIKE', $d)
                    ->orWhere('ip_address', 'LIKE', $d);
            });
        }

        $logs = $query->latest()->paginate(50)->withQueryString();
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
            // Normalize Persian/Arabic digits to Latin (datepicker/keyboard may output Persian digits,
            // and (int) cast of Persian digits becomes 0 which silently dropped the date filter)
            $jalaliDate = str_replace(
                ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                $jalaliDate
            );
            $jalaliDate = str_replace('/', '-', $jalaliDate);
            $jalaliDate = preg_replace('/\s+/', '', $jalaliDate);
            $parts = explode('-', $jalaliDate);
            if (count($parts) < 3) return null;
            $y = (int) $parts[0];
            $m = (int) $parts[1];
            $d = (int) $parts[2];
            if ($y < 100) $y += 1300;
            if ($y < 1300 || $y > 1500 || $m < 1 || $m > 12 || $d < 1 || $d > 31) return null;
            return Jalalian::fromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $y, $m, $d))
                ->toCarbon()->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
