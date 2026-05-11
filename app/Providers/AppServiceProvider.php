<?php

namespace App\Providers;

use App\Models\DailyLog;
use App\Models\Goal;
use App\Models\JournalEntry;
use App\Models\Routine;
use App\Policies\DailyLogPolicy;
use App\Policies\GoalPolicy;
use App\Policies\JournalEntryPolicy;
use App\Policies\RoutinePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiting();

        Gate::policy(Routine::class, RoutinePolicy::class);
        Gate::policy(DailyLog::class, DailyLogPolicy::class);
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);
        Gate::policy(Goal::class, GoalPolicy::class);
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $perMinute = (int) config('vitalflow.api_throttle_per_minute', 120);

            return Limit::perMinute($perMinute)
                ->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            $perMinute = (int) config('vitalflow.auth_throttle_per_minute', 10);

            return Limit::perMinute($perMinute)->by($request->ip());
        });
    }
}
