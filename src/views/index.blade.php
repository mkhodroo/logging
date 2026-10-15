@extends('behin-layouts.app')
@section('content')
<div class="container-fluid" dir="rtl">
<div class="card">
<div class="card-header bg-primary text-white"><h4>گزارش فعالیت کاربران</h4></div>
<div class="card-body">
<form method="GET" action="{{ route('user-action-logs.index') }}" class="mb-3">
<div class="row">
<div class="col-md-2 mb-2"><label>شناسه کاربر</label><input name="user_id" value="{{ request('user_id') }}" class="form-control"></div>
<div class="col-md-2 mb-2"><label>نام کاربر</label><input name="user_name" value="{{ request('user_name') }}" class="form-control"></div>
<div class="col-md-2 mb-2"><label>آدرس عملیات</label><input name="path" value="{{ request('path') }}" class="form-control" dir="ltr"></div>
<div class="col-md-2 mb-2"><label>متد</label><select name="method" class="form-control"><option value="">همه</option><option value="GET"{{ request('method')=='GET'?' selected':'' }}>GET</option><option value="POST"{{ request('method')=='POST'?' selected':'' }}>POST</option><option value="PUT"{{ request('method')=='PUT'?' selected':'' }}>PUT</option><option value="PATCH"{{ request('method')=='PATCH'?' selected':'' }}>PATCH</option><option value="DELETE"{{ request('method')=='DELETE'?' selected':'' }}>DELETE</option></select></div>
<div class="col-md-2 mb-2"><label>از تاریخ (شمسی)</label><input name="date_from" value="{{ request('date_from') }}" class="form-control persian-date" autocomplete="off"></div>
<div class="col-md-2 mb-2"><label>تا تاریخ (شمسی)</label><input name="date_to" value="{{ request('date_to') }}" class="form-control persian-date" autocomplete="off"></div>
<div class="col-md-6 mb-2"><label>جزئیات لاگ</label><input name="detail" value="{{ request('detail') }}" class="form-control"></div>
<div class="col-md-6 mb-2 d-flex align-items-end"><button class="btn btn-primary ml-2">فیلتر</button><a href="{{ route('user-action-logs.index') }}" class="btn btn-secondary">حذف فیلتر</a></div>
</div>
</form>

<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead><tr><th>#</th><th>کاربر</th><th>عملیات</th><th>آدرس</th><th>کد پاسخ</th><th>IP</th><th>تاریخ (شمسی)</th><th>نمایش</th></tr></thead>
<tbody>
@forelse($logs as $log)
<tr>
<td>{{ $log->id }}</td>
<td>{{ $log->user_name }} ({{ $log->user_id }})</td>
<td><span class="badge badge-info">{{ $log->method }}</span> {{ $log->action }}</td>
<td dir="ltr" class="text-left">{{ $log->path }}</td>
<td>{{ $log->status_code }}</td>
<td dir="ltr">{{ $log->ip_address }}</td>
<td>{{ $log->created_at ? \Morilog\Jalali\Jalalian::fromCarbon($log->created_at)->format('Y/m/d H:i:s') : '-' }}</td>
<td><button type="button" class="btn btn-sm btn-outline-primary" onclick="showLogDetail({{ $log->id }})">جزئیات</button></td>
</tr>
@empty
<tr><td colspan="8" class="text-center">لاگی ثبت نشده است.</td></tr>
@endforelse
</tbody>
</table>
</div>
{{ $logs->links() }}
</div>
</div>
</div>
@endsection

<div class="modal fade" id="logDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
<div class="modal-dialog modal-lg" role="document">
<div class="modal-content">
<div class="modal-header bg-primary text-white">
<h5 class="modal-title mb-0" id="logDetailModalLabel">جزئیات لاگ</h5>
<button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
</div>
<div class="modal-body" dir="rtl">
<div id="logDetailLoading" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>
<div id="logDetailContent" style="display:none;">
<table class="table table-bordered table-sm">
<tr><th style="width:150px;">کاربر</th><td id="ld-user"></td></tr>
<tr><th>متد</th><td id="ld-method"></td></tr>
<tr><th>اکشن</th><td dir="ltr" class="text-left" id="ld-action"></td></tr>
<tr><th>آدرس</th><td dir="ltr" class="text-left" id="ld-path"></td></tr>
<tr><th>کد پاسخ</th><td id="ld-status"></td></tr>
<tr><th>IP</th><td dir="ltr" class="text-left" id="ld-ip"></td></tr>
<tr><th>مرورگر</th><td dir="ltr" class="text-left" style="word-break:break-all;" id="ld-agent"></td></tr>
<tr><th>تاریخ (شمسی)</th><td id="ld-date"></td></tr>
</table>
<h6 class="mt-3">پارامترها / جزئیات لاگ</h6>
<pre id="ld-params" dir="ltr" class="text-left bg-light p-3 rounded" style="max-height:400px;overflow:auto;"></pre>
</div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button></div>
</div>
</div>
</div>

@section('script')
<script>
$(document).ready(function() { if (typeof initial_view === 'function') { initial_view(); } });
function showLogDetail(id) {
$('#logDetailLoading').show();
$('#logDetailContent').hide();
$('#logDetailModal').modal('show');
$.ajax({
url: '{{ url('user-action-logs') }}/' + id,
type: 'GET', dataType: 'json',
headers: { 'X-Requested-With': 'XMLHttpRequest' },
success: function(data) {
$('#ld-user').text((data.user_name || '-') + ' (' + (data.user_id || '-') + ')');
$('#ld-method').text(data.method || '-');
$('#ld-action').text(data.action || '-');
$('#ld-path').text(data.path || '-');
$('#ld-status').text(data.status_code || '-');
$('#ld-ip').text(data.ip_address || '-');
$('#ld-agent').text(data.user_agent || '-');
$('#ld-date').text(data.created_at_jalali || '-');
$('#ld-params').text(data.params_pretty || 'پارامتری ثبت نشده است.');
$('#logDetailModalLabel').text('جزئیات لاگ #' + data.id);
$('#logDetailLoading').hide();
$('#logDetailContent').show();
},
error: function() { $('#logDetailLoading').html('<div class="alert alert-danger">خطا در دریافت جزئیات لاگ.</div>'); }
});
}
</script>
@endsection

