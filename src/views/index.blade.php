@extends('behin-layouts.app')
@section('content')
<div class="container-fluid" dir="rtl">
    <div class="card">
        <div class="card-header bg-primary text-white"><h4>گزارش فعالیت کاربران</h4></div>
        <div class="card-body">
            <form method="GET" action="{{ route('user-action-logs.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-2 mb-2">
                        <label>شناسه کاربر</label>
                        <input name="user_id" value="{{ request('user_id') }}" class="form-control" placeholder="شناسه کاربر">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>نام کاربر</label>
                        <input name="user_name" value="{{ request('user_name') }}" class="form-control" placeholder="نام کاربر">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>آدرس عملیات</label>
                        <input name="path" value="{{ request('path') }}" class="form-control" placeholder="مثلا: admin/users" dir="ltr">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>متد</label>
                        <select name="method" class="form-control">
                            <option value="">همه</option>
                            @foreach(['GET','POST','PUT','PATCH','DELETE'] as $m)
                                <option value="{{ $m }}" {{ request('method')==$m ? 'selected':'' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>از تاریخ</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>تا تاریخ</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label>جزئیات لاگ (جستجو در اکشن، پارامترها، IP، مرورگر)</label>
                        <input name="detail" value="{{ request('detail') }}" class="form-control" placeholder="متن داخل لاگ...">
                    </div>
                    <div class="col-md-6 mb-2 d-flex align-items-end gap-2">
                        <button class="btn btn-primary ml-2">فیلتر</button>
                        <a href="{{ route('user-action-logs.index') }}" class="btn btn-secondary">حذف فیلتر</a>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>کاربر</th>
                            <th>عملیات</th>
                            <th>آدرس</th>
                            <th>جزئیات</th>
                            <th>کد پاسخ</th>
                            <th>IP</th>
                            <th>تاریخ</th>
                            <th>نمایش</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->user_name }} ({{ $log->user_id }})</td>
                            <td><span class="badge badge-info">{{ $log->method }}</span> {{ $log->action }}</td>
                            <td dir="ltr" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $log->path }}">{{ $log->path }}</td>
                            <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ is_array($log->params) ? json_encode($log->params, JSON_UNESCAPED_UNICODE) : $log->params }}">{{ is_array($log->params) ? \Illuminate\Support\Str::limit(json_encode($log->params, JSON_UNESCAPED_UNICODE), 100) : \Illuminate\Support\Str::limit($log->params ?? '', 100) }}</td>
                            <td>{{ $log->status_code }}</td>
                            <td dir="ltr">{{ $log->ip_address }}</td>
                            <td>{{ $log->created_at }}</td>
                            <td><a href="{{ route('user-action-logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary">جزئیات</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">لاگی ثبت نشده است.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
