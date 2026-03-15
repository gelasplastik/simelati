<x-layouts.app :title="'Upload Modul Ajar'" :pageTitle="'Upload Modul Ajar'" :breadcrumb="'Teacher / Modul Ajar / Upload'">
    <x-panel>
        <form method="POST" action="{{ route('teacher.modules.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            @include('teacher.teaching_modules._form')

            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Kirim Modul</button>
                <a href="{{ route('teacher.modules.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </x-panel>
</x-layouts.app>
