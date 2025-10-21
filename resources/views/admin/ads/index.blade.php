@extends('admin.master')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <h4>Ads List</h4>
        <a href="{{ route('ads.create') }}" class="btn btn-primary">Add New Ad</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            @if($ads->isEmpty())
                <p class="text-center">No ads found.</p>
            @else
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Show MRCE Ads</th>
                        <th>Show Button Timer Ads</th>
                        <th>Show Banner Ads</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ads as $index => $ad)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $ad->code }}</td>
                        <td>{{ ucfirst($ad->show_mrce_ads) }}</td>
                        <td>{{ ucfirst($ad->show_button_timer_ads) }}</td>
                        <td>{{ ucfirst($ad->show_banner_ads) }}</td>
                        <td>
                            <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('ads.destroy', $ad->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
