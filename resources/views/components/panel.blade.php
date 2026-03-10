<div class="card sim-card border-0 {{ $class ?? '' }}">
    @isset($title)
    <div class="card-header bg-white border-0 pt-3 pb-0"><strong>{{ $title }}</strong></div>
    @endisset
    <div class="card-body">{{ $slot }}</div>
</div>
