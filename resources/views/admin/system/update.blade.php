<x-layouts.app title="Application Update Center" page-title="SIMELATI Application Update Center" breadcrumb="Admin / System / Application Update">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="alert alert-warning mb-0">
                <strong>Peringatan:</strong> Halaman ini khusus maintenance sistem produksi. Jalankan hanya saat diperlukan.
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Aksi Update</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" data-action="clear_cache" data-label="Clear Cache" data-command="php artisan optimize:clear">Clear Cache</button>
                        <button type="button" class="btn btn-outline-primary" data-action="migrate" data-label="Run Migration" data-command="php artisan migrate --force">Run Migration</button>
                        <button type="button" class="btn btn-outline-primary" data-action="seed" data-label="Run Seeder" data-command="php artisan db:seed --force">Run Seeder</button>
                        <button type="button" class="btn btn-outline-primary" data-action="sync_national" data-label="Sync National Holidays" data-command="php artisan calendar:sync-national --year={{ now()->year }}">Sync National Holidays</button>
                        <button type="button" class="btn btn-danger" data-action="full_update" data-label="Full System Update" data-command="optimize:clear -> migrate --force -> db:seed --force -> calendar:sync-national">Full System Update</button>
                        <button type="button" class="btn btn-outline-secondary" data-action="git_pull" data-label="Update Code From Git" data-command="git pull" {{ $gitPullSupported ? '' : 'disabled' }}>
                            Update Code From Git
                        </button>
                    </div>
                    @unless($gitPullSupported)
                        <div class="alert alert-info mt-3 mb-0">Server tidak mengizinkan shell command (`exec/shell_exec`), jadi tombol Git pull dinonaktifkan.</div>
                    @endunless
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Output Terakhir</div>
                <div class="card-body">
                    @if($latestOutput)
                        <div class="mb-2">
                            <span class="badge {{ $latestStatus === 'success' ? 'text-bg-success' : 'text-bg-danger' }}">{{ strtoupper($latestStatus) }}</span>
                            <span class="small text-secondary ms-2">{{ $latestAction }}</span>
                        </div>
                        <pre class="bg-dark text-light p-2 rounded small" style="max-height:280px; overflow:auto;">{{ $latestOutput }}</pre>
                    @else
                        <div class="text-secondary small">Belum ada output pada sesi ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Riwayat Update (10 terakhir)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aksi</th>
                            <th>Perintah</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Output</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="small">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $log->action }}</td>
                                <td><code class="small">{{ $log->command }}</code></td>
                                <td>
                                    <span class="badge {{ $log->status === 'success' ? 'text-bg-success' : 'text-bg-danger' }}">{{ strtoupper($log->status) }}</span>
                                </td>
                                <td class="small">{{ $log->user?->name ?? '-' }}</td>
                                <td>
                                    <details>
                                        <summary class="small">Lihat output</summary>
                                        <pre class="bg-dark text-light p-2 rounded small mt-2" style="max-width: 580px; max-height: 220px; overflow:auto;">{{ $log->output }}</pre>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada log update.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.system.update.run') }}" class="vstack gap-2">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Eksekusi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="update_action_field">
                        <div class="mb-2 small text-secondary">Aksi:</div>
                        <div class="fw-semibold" id="update_action_label">-</div>
                        <div class="mt-2">
                            <div class="small text-secondary mb-1">Command:</div>
                            <code id="update_command_label">-</code>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="confirm" id="confirm_update" value="1" required>
                            <label class="form-check-label" for="confirm_update">Saya paham risiko dan ingin menjalankan aksi ini.</label>
                        </div>
                        @if($tokenEnabled)
                            <div class="mt-3">
                                <label class="form-label">Deploy Token</label>
                                <input type="password" class="form-control" name="deploy_token" required>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Jalankan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('updateConfirmModal');
            const modal = new bootstrap.Modal(modalEl);
            const actionField = document.getElementById('update_action_field');
            const actionLabel = document.getElementById('update_action_label');
            const commandLabel = document.getElementById('update_command_label');

            document.querySelectorAll('[data-action]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    actionField.value = btn.dataset.action;
                    actionLabel.textContent = btn.dataset.label;
                    commandLabel.textContent = btn.dataset.command;
                    modal.show();
                });
            });
        });
    </script>
</x-layouts.app>

