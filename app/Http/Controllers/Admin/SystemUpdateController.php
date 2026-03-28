<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemUpdateLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SystemUpdateController extends Controller
{
    public function index()
    {
        $logs = SystemUpdateLog::query()
            ->with('user:id,name,email')
            ->latest('id')
            ->limit(10)
            ->get();

        $latestOutput = session('update_output');
        $latestStatus = session('update_status');
        $latestAction = session('update_action');

        return view('admin.system.update', [
            'logs' => $logs,
            'latestOutput' => $latestOutput,
            'latestStatus' => $latestStatus,
            'latestAction' => $latestAction,
            'gitPullSupported' => $this->isShellExecutionAvailable(),
            'tokenEnabled' => filled(config('system_update.confirmation_token')),
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:clear_cache,migrate,seed,sync_national,full_update,git_pull'],
            'confirm' => ['required', 'accepted'],
            'deploy_token' => ['nullable', 'string'],
        ], [
            'confirm.accepted' => 'Konfirmasi wajib dicentang sebelum menjalankan perintah.',
        ]);

        $requiredToken = config('system_update.confirmation_token');
        if (filled($requiredToken) && ! hash_equals((string) $requiredToken, (string) ($validated['deploy_token'] ?? ''))) {
            return back()->withErrors([
                'deploy_token' => 'Deploy token tidak valid.',
            ]);
        }

        $result = match ($validated['action']) {
            'clear_cache' => $this->runArtisanAction('Clear Cache', ['optimize:clear']),
            'migrate' => $this->runArtisanAction('Run Migration', ['migrate --force']),
            'seed' => $this->runArtisanAction('Run Seeder', ['db:seed --force']),
            'sync_national' => $this->runArtisanAction('Sync National Holidays', ['calendar:sync-national --year='.now()->year]),
            'full_update' => $this->runArtisanAction('Full System Update', [
                'optimize:clear',
                'migrate --force',
                'db:seed --force',
                'calendar:sync-national --year='.now()->year,
            ]),
            'git_pull' => $this->runGitPull(),
        };

        SystemUpdateLog::create([
            'user_id' => $request->user()->id,
            'action' => $result['action'],
            'command' => $result['command'],
            'output' => Str::limit($result['output'], 65000, "\n...[truncated]"),
            'status' => $result['success'] ? 'success' : 'failed',
            'created_at' => now(),
        ]);

        return redirect()
            ->route('admin.system.update.index')
            ->with('update_action', $result['action'])
            ->with('update_status', $result['success'] ? 'success' : 'failed')
            ->with('update_output', $result['output'])
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function runArtisanAction(string $action, array $commands): array
    {
        $outputs = [];
        $success = true;

        foreach ($commands as $command) {
            $exit = Artisan::call($command);
            $output = trim((string) Artisan::output());
            $outputs[] = "[artisan] {$command}";
            $outputs[] = $output !== '' ? $output : '(no output)';
            $outputs[] = "exit_code={$exit}";
            $outputs[] = str_repeat('-', 48);

            if ($exit !== 0) {
                $success = false;
                break;
            }
        }

        return [
            'action' => $action,
            'command' => implode("\n", $commands),
            'output' => implode("\n", $outputs),
            'success' => $success,
            'message' => $success ? "{$action} berhasil dijalankan." : "{$action} gagal dijalankan.",
        ];
    }

    private function runGitPull(): array
    {
        if (! $this->isShellExecutionAvailable()) {
            return [
                'action' => 'Update Code From Git',
                'command' => 'git pull',
                'output' => 'shell_exec/exec dinonaktifkan oleh server hosting.',
                'success' => false,
                'message' => 'Server tidak mengizinkan eksekusi shell command.',
            ];
        }

        $repoPath = base_path();
        $command = 'cd '.escapeshellarg($repoPath).' && git pull 2>&1';
        $output = shell_exec($command);
        $output = trim((string) $output);

        $failed = $output === '' || Str::contains(strtolower($output), [
            'fatal:',
            'error:',
            'not a git repository',
        ]);

        return [
            'action' => 'Update Code From Git',
            'command' => 'git pull',
            'output' => $output !== '' ? $output : '(no output)',
            'success' => ! $failed,
            'message' => $failed ? 'Git pull gagal atau diblokir server.' : 'Git pull berhasil dijalankan.',
        ];
    }

    private function isShellExecutionAvailable(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $disabled = (string) ini_get('disable_functions');
        if ($disabled === '') {
            return true;
        }

        $disabledFunctions = array_map('trim', explode(',', $disabled));

        return ! in_array('shell_exec', $disabledFunctions, true)
            && ! in_array('exec', $disabledFunctions, true);
    }
}

