<?php

namespace App\Traits;

use App\Services\AlertService;
use Illuminate\Support\Facades\App;

trait LogsSensitiveActions
{
    protected function logSensitiveAction(string $action, array $context = []): void
    {
        if ($user = auth()->user()) {
            App::make(AlertService::class)->logSensitiveAction($user, $action, $context);
        }
    }
}
