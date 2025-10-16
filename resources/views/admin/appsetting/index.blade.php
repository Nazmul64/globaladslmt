@extends('admin.master')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>App Settings List</h4>
        <a href="{{ route('appsetting.create') }}" class="btn btn-success">
            <i class="bi bi-arrow-left-circle"></i> Back to Create
        </a>
    </div>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>App Theme</th>
                <th>Home Icon</th>
                <th>Currency</th>
                <th>Status</th>
                <th>Version</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appsettings as $setting)
                <tr>
                    <td>{{ $setting->id }}</td>
                    <td>{{ $setting->app_theme }}</td>
                    <td>{{ $setting->home_icon_themes }}</td>
                    <td>{{ $setting->currency_symbol }}</td>
                    <td>{{ $setting->enabled }}</td>
                    <td>{{ $setting->app_version }}</td>
                    <td>
                        <a href="{{ route('appsetting.edit', $setting->id) }}" class="btn btn-sm btn-primary" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </a>

                        <form action="{{ route('appsetting.destroy', $setting->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this setting?')" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No App Settings found!</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
