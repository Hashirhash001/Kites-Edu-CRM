<?php

namespace App\Providers;

use App\Models\EduLead;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('eduLead', function (string $value) {
            $query = EduLead::query();
            $name  = request()->route()?->getName() ?? '';

            $preLeadScoped = [
                'edu-pre-leads.show', 'edu-pre-leads.edit', 'edu-pre-leads.update',
                'edu-pre-leads.destroy', 'edu-pre-leads.convert', 'edu-pre-leads.assign',
            ];
            $leadScoped = [
                'edu-leads.show', 'edu-leads.edit', 'edu-leads.update',
                'edu-leads.destroy', 'edu-leads.assign',
            ];

            if (in_array($name, $preLeadScoped, true)) {
                $query->where('is_pre_lead', true);
            } elseif (in_array($name, $leadScoped, true)) {
                $query->where('is_pre_lead', false);
            }

            return $query->findOrFail($value);
        });

        // Check role blade directive
        Blade::if('role', function ($role) {
            return optional(auth()->user())->hasRole($role);
        });

        // Super Admin blade directive
        Blade::if('superadmin', function () {
            return optional(auth()->user())->isSuperAdmin();
        });

        // Lead Manager blade directive
        Blade::if('leadmanager', function () {
            return optional(auth()->user())->isLeadManager();
        });

        // Field Staff blade directive
        Blade::if('fieldstaff', function () {
            return optional(auth()->user())->isFieldStaff();
        });

        // Reporting User blade directive
        Blade::if('reportinguser', function () {
            return optional(auth()->user())->isReportingUser();
        });
    }
}
