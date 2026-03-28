<?php

namespace App\Providers;

use App\Domain\MasterData\AcademicCalendarService;
use App\Domain\Reporting\AdminNotificationService;
use App\Domain\Reporting\DutyAttendanceVerificationAlertService;
use App\Domain\Reporting\TeacherJournalNotificationService;
use App\Domain\Reporting\TeacherModuleNotificationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
            $teacherPayload = null;
            $teacherModulePayload = null;
            $teacherDutyAttendanceAlerts = null;
            $adminPayload = null;

            if ($user?->role === 'teacher' && $user->teacher) {
                $teacherPayload = app(TeacherJournalNotificationService::class)->buildToday($user->teacher);
                $teacherModulePayload = app(TeacherModuleNotificationService::class)->buildForTeacher($user->teacher);
                $teacherDutyAttendanceAlerts = app(DutyAttendanceVerificationAlertService::class)->buildForTeacher($user->teacher);
            }

            if (in_array($user?->role, ['admin', 'superadmin'], true)) {
                $adminPayload = app(AdminNotificationService::class)->buildForUser($user);
            }

            $view->with('topbarTeacherNotifications', $teacherPayload);
            $view->with('topbarTeacherModuleNotifications', $teacherModulePayload);
            $view->with('topbarTeacherDutyAttendanceAlerts', $teacherDutyAttendanceAlerts);
            $view->with('topbarAdminNotifications', $adminPayload);
        });

        View::composer(['components.layouts.app', 'layouts.app'], function ($view) {
            $notice = app(AcademicCalendarService::class)->buildDashboardNoticeForDate(now()->toDateString());
            $view->with('todayCalendarNotice', $notice);
        });
    }
}

