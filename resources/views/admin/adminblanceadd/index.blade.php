@extends('admin.master')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="userSearch" class="form-control" placeholder="Search by Name, Email, or Mobile..." onkeyup="searchTable()">
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Manual Deposit - User List</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="userTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
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
                                <td>{{ $user->mobile ?? $user->phone ?? 'N/A' }}</td>
                                <td>{{ optional($user->created_at)->format('d M, Y') ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.balance.edit', $user->id) }}" class="btn btn-success btn-sm">Edit / Add Balance</a>
                                    <form action="{{ route('admin.usrdelete', $user->id) }}" method="POST" style="display:inline-block;">
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
        let tdMobile = tr[i].getElementsByTagName("td")[3]; // Mobile column
        if (tdName && tdEmail) {
            let txtValueName = tdName.textContent || tdName.innerText;
            let txtValueEmail = tdEmail.textContent || tdEmail.innerText;
            let txtValueMobile = tdMobile ? (tdMobile.textContent || tdMobile.innerText) : '';
            if (txtValueName.toLowerCase().includes(filter) || txtValueEmail.toLowerCase().includes(filter) || txtValueMobile.toLowerCase().includes(filter)) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>
@endsection
