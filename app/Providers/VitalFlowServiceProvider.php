<?php

namespace App\Providers;

use App\Contracts\AiAnalysisContract;
use App\Services\Ai\OpenAiAnalysisService;
use Illuminate\Support\ServiceProvider;

class VitalFlowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AiAnalysisContract::class, OpenAiAnalysisService::class);
    }

    public function boot(): void
    {
        //
    }
}
