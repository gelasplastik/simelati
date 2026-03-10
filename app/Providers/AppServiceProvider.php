<?php

namespace App\Providers;

use App\Domain\Reporting\TeacherJournalNotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('partials.topbar', function ($view) {
            $user = Auth::user();
            $payload = null;

            if ($user?->role === 'teacher' && $user->teacher) {
                $payload = app(TeacherJournalNotificationService::class)->buildToday($user->teacher);
            }

            $view->with('topbarTeacherNotifications', $payload);
        });
    }
}
