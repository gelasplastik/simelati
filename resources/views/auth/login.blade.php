<x-layouts.guest :title="'SIMELATI - Login'">
    <form method="POST" action="{{ route('login.store') }}" class="vstack gap-3">
        @csrf
        <div>
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <div>
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ingat Saya</label>
        </div>
        <button class="btn btn-primary btn-action-lg w-100" type="submit">Masuk ke SIMELATI</button>
    </form>
    <div class="text-center mt-3">
        <a href="{{ route('public.permissions.create') }}" class="btn btn-link text-decoration-none">Ajukan Izin Siswa Tanpa Login</a>
    </div>
</x-layouts.guest>
