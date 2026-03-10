@if(isset($errors) && $errors->any())
    <div class="alert alert-danger shadow-sm">
        <div class="fw-semibold mb-1">Periksa kembali isian form:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
