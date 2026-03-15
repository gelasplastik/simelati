<x-layouts.app :title="'Edit Modul Ajar'" :pageTitle="'Edit Modul Ajar'" :breadcrumb="'Teacher / Modul Ajar / Edit'">
    <x-panel>
        <form method="POST" action="{{ route('teacher.modules.update', $module) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            @method('PUT')
            @include('teacher.teaching_modules._form', ['module' => $module])

            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                <a href="{{ route('teacher.modules.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </x-panel>
</x-layouts.app>
