<x-layouts.app :title="'Teachers'" :pageTitle="'Teachers'" :breadcrumb="'Admin / Master Data / Teachers'">
    <x-panel title="Tambah Guru" class="mb-3">
        <form method="POST" action="{{ route('admin.teachers.store') }}" class="row g-2">
            @csrf
            <div class="col-md-3"><input class="form-control" name="name" placeholder="Nama" required></div>
            <div class="col-md-3"><input type="email" class="form-control" name="email" placeholder="Email" required></div>
            <div class="col-md-2"><input class="form-control" name="employee_code" placeholder="NIP/Kode" required></div>
            <div class="col-md-2"><input type="password" class="form-control" name="password" placeholder="Password" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
        </form>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Nama</th><th>Email</th><th>Kode</th><th>Update</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ $item->user->email }}</td>
                    <td>{{ $item->employee_code }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.teachers.update', $item) }}" class="row g-1">
                            @csrf @method('PUT')
                            <div class="col-md-3"><input class="form-control form-control-sm" name="name" value="{{ $item->user->name }}" required></div>
                            <div class="col-md-3"><input class="form-control form-control-sm" name="email" value="{{ $item->user->email }}" required></div>
                            <div class="col-md-2"><input class="form-control form-control-sm" name="employee_code" value="{{ $item->employee_code }}" required></div>
                            <div class="col-md-2"><input class="form-control form-control-sm" name="password" placeholder="Password baru"></div>
                            <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100">Update</button></div>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
