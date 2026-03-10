<x-layouts.app :title="'Subjects'" :pageTitle="'Subjects'" :breadcrumb="'Admin / Master Data / Subjects'">
    <x-panel><table class="table table-sm"><thead><tr><th>Mata Pelajaran</th></tr></thead><tbody>@foreach($items as $item)<tr><td>{{ $item->name }}</td></tr>@endforeach</tbody></table>{{ $items->links() }}</x-panel>
</x-layouts.app>
