@extends('admin.master')
@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="userSearch" class="form-control" placeholder="Search by Name or Email..." onkeyup="searchTable()">
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>User List</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="userTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created At</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at }}</td>
                                <td>
                                    <form action="{{ route('users.updateStatus', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <select name="is_blocked" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="0" {{ $user->is_blocked == 0 ? 'selected' : '' }}>Active</option>
                                            <option value="1" {{ $user->is_blocked == 1 ? 'selected' : '' }}>Block</option>
                                        </select>
                                    </form>
                                </td>

                                <td>
                                   <a href="{{ route('admin.impersonate', $user->id) }}" class="btn btn-sm btn-info">
                                        👁️ View User
                                    </a>
                                    <form action="{{route('admin.usrdelete',$user->id)}}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function searchTable() {
    let input = document.getElementById("userSearch");
    let filter = input.value.toLowerCase();
    let table = document.getElementById("userTable");
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) { // skip header row
        let tdName = tr[i].getElementsByTagName("td")[1]; // Name column
        let tdEmail = tr[i].getElementsByTagName("td")[2]; // Email column
        if (tdName || tdEmail) {
            let txtValueName = tdName.textContent || tdName.innerText;
            let txtValueEmail = tdEmail.textContent || tdEmail.innerText;
            if (txtValueName.toLowerCase().indexOf(filter) > -1 || txtValueEmail.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

@endsection




