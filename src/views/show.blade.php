@extends('behin-layouts.app')
@section('content')
<div class="container-fluid" dir="rtl">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">جزئیات لاگ #{{ $log->id }}</h4>
            <a href="{{ route('user-action-logs.index', request()->query()) }}" class="btn btn-light btn-sm">بازگشت به لیست</a>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr><th style="width:180px;">کاربر</th><td>{{ $log->user_name }} ({{ $log->user_id }})</td></tr>
                <tr><th>متد</th><td><span class="badge badge-info">{{ $log->method }}</span></td></tr>
                <tr><th>اکشن</th><td dir="ltr" class="text-left">{{ $log->action }}</td></tr>
                <tr><th>آدرس</th><td dir="ltr" class="text-left">{{ $log->path }}</td></tr>
                <tr><th>کد پاسخ</th><td>{{ $log->status_code }}</td></tr>
                <tr><th>IP</th><td dir="ltr" class="text-left">{{ $log->ip_address }}</td></tr>
                <tr><th>مرورگر</th><td dir="ltr" class="text-left" style="word-break:break-all;">{{ $log->user_agent }}</td></tr>
                <tr><th>تاریخ</th><td>{{ $log->created_at }}</td></tr>
            </table>
            <h5 class="mt-4">پارامترها / جزئیات لاگ</h5>
            @if($log->params)
                <pre dir="ltr" class="text-left bg-light p-3 rounded" style="max-height:500px;overflow:auto;">{{ json_encode($log->params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <div class="alert alert-secondary">پارامتری ثبت نشده است.</div>
            @endif
        </div>
    </div>
</div>
@endsection
