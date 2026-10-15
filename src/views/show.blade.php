@extends('behin-layouts.app')
@section('content')
<div class="container-fluid" dir="rtl">
<div class="card">
<div class="card-header bg-primary text-white"><h4>جزئیات لاگ #{{ $log->id }}</h4></div>
<div class="card-body">
<table class="table table-bordered">
<tr><th style="width:180px;">کاربر</th><td>{{ $log->user_name }} ({{ $log->user_id }})</td></tr>
<tr><th>متد</th><td>{{ $log->method }}</td></tr>
<tr><th>اکشن</th><td dir="ltr" class="text-left">{{ $log->action }}</td></tr>
<tr><th>آدرس</th><td dir="ltr" class="text-left">{{ $log->path }}</td></tr>
<tr><th>کد پاسخ</th><td>{{ $log->status_code }}</td></tr>
<tr><th>IP</th><td dir="ltr">{{ $log->ip_address }}</td></tr>
<tr><th>مرورگر</th><td dir="ltr" class="text-left">{{ $log->user_agent }}</td></tr>
<tr><th>تاریخ (شمسی)</th><td>{{ $jalaliDate ?? '-' }}</td></tr>
</table>
<h5 class="mt-4">پارامترها</h5>
@if(!empty($paramsPretty))
<pre dir="ltr" class="text-left bg-light p-3">{{ $paramsPretty }}</pre>
@else
<div class="alert alert-secondary">پارامتری ثبت نشده است.</div>
@endif
<a href="{{ route('user-action-logs.index') }}" class="btn btn-secondary mt-3">بازگشت</a>
</div>
</div>
</div>
@endsection
