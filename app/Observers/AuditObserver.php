<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function __construct(protected AuditService $auditService) {}

    public function created(Model $model): void
    {
        if (!$this->shouldLog($model)) return;

        $this->auditService->logModelEvent('created', $model, null, $model->toArray());
    }

    public function updated(Model $model): void
    {
        if (!$this->shouldLog($model)) return;

        $original = $model->getOriginal();
        $changes = $model->getDirty();

        if (empty($changes)) return;

        $action = $this->auditService->detectAction($original, $changes);

        $this->auditService->logModelEvent($action, $model, $original, $model->toArray());
    }

    public function deleted(Model $model): void
    {
        if (!$this->shouldLog($model)) return;

        $this->auditService->logModelEvent('deleted', $model, $model->toArray(), null);
    }

    public function restored(Model $model): void
    {
        if (!$this->shouldLog($model)) return;

        $this->auditService->logModelEvent('restored', $model, null, $model->toArray());
    }

    protected function shouldLog(Model $model): bool
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return false;
        }

        return true;
    }
}
