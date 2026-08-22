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
                                    <a href="{{ route('admin.deposite.edit', $user->id) }}" class="btn btn-success btn-sm">Deposite Balance Add & Edit</a>
                                    <form action="{{ route('admin.usrdelete', $user->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">Delete</button>
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
    let input = document.getElementById("userSearch").value.toLowerCase();
    let table = document.getElementById("userTable");
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let tdName = tr[i].getElementsByTagName("td")[1];
        let tdEmail = tr[i].getElementsByTagName("td")[2];
        if(tdName && tdEmail){
            let txtName = tdName.textContent || tdName.innerText;
            let txtEmail = tdEmail.textContent || tdEmail.innerText;
            tr[i].style.display = txtName.toLowerCase().includes(input) || txtEmail.toLowerCase().includes(input) ? "" : "none";
        }
    }
}
</script>
@endsection
