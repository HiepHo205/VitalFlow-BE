<?php

namespace App\Providers;

use App\Contracts\AiAnalysisContract;
use App\Services\Ai\HeuristicAiAnalysisService;
use Illuminate\Support\ServiceProvider;

class VitalFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiAnalysisContract::class, HeuristicAiAnalysisService::class);
    }

    public function boot(): void
    {
        //
    }
}
