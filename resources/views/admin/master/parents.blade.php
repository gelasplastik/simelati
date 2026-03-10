<x-layouts.app :title="'Parents'" :pageTitle="'Parents'" :breadcrumb="'Admin / Master Data / Parents'">
    <x-panel>
        <table class="table table-sm">
            <thead><tr><th>Nama</th><th>Email</th><th>Phone</th><th>Anak</th></tr></thead>
            <tbody>@foreach($items as $item)<tr><td>{{ $item->user->name }}</td><td>{{ $item->user->email }}</td><td>{{ $item->phone }}</td><td>{{ $item->students->pluck('full_name')->join(', ') }}</td></tr>@endforeach</tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
