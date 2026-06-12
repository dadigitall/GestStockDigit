<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function log(array $data): AuditLog
    {
        $user = auth()->user();

        return AuditLog::create([
            'company_id' => $data['company_id'] ?? $user?->company_id ?? throw new \RuntimeException('company_id requis'),
            'user_id' => $data['user_id'] ?? $user?->id,
            'user_name' => $data['user_name'] ?? $user?->name,
            'action' => $data['action'],
            'module' => $data['module'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'old_values' => isset($data['old_values']) ? $this->sanitize($data['old_values']) : null,
            'new_values' => isset($data['new_values']) ? $this->sanitize($data['new_values']) : null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'result' => $data['result'] ?? 'success',
            'reason' => $data['reason'] ?? null,
        ]);
    }

    public function logModelEvent(string $action, Model $model, ?array $oldValues = null, ?array $newValues = null, ?string $reason = null): AuditLog
    {
        $module = $this->guessModule($model);
        $user = auth()->user();

        return $this->log([
            'company_id' => $model?->company_id ?? $user?->company_id,
            'action' => $action,
            'module' => $module,
            'entity_type' => get_class($model),
            'entity_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reason' => $reason,
        ]);
    }

    public function logAuthEvent(string $action, $user = null, ?string $reason = null): AuditLog
    {
        $user ??= auth()->user();

        return $this->log([
            'company_id' => $user?->company_id ?? 0,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Inconnu',
            'action' => $action,
            'module' => 'auth',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'result' => $action === 'failed_login' ? 'failure' : 'success',
            'reason' => $reason,
        ]);
    }

    public function detectAction(array $original, array $changes): string
    {
        $priceFields = ['sale_price', 'purchase_price', 'wholesale_price', 'promo_price'];
        $stockFields = ['stock_quantity'];
        $statusActions = [
            'cancelled' => ['cancelled', 'refunded', 'rejected'],
            'validated' => ['completed', 'received', 'paid', 'sent', 'validated', 'approved', 'accepted', 'confirmed'],
            'closed' => ['closed'],
        ];

        if (array_key_exists('status', $changes)) {
            $oldStatus = $original['status'] ?? '';
            $newStatus = $changes['status'] ?? '';
            if ($oldStatus !== $newStatus) {
                foreach ($statusActions as $action => $statuses) {
                    if (in_array($newStatus, $statuses) && !in_array($oldStatus, $statuses)) {
                        return $action;
                    }
                }
            }
        }

        foreach ($priceFields as $field) {
            if (array_key_exists($field, $changes) && $original[$field] != $changes[$field]) {
                return 'price_changed';
            }
        }

        foreach ($stockFields as $field) {
            if (array_key_exists($field, $changes) && $original[$field] != $changes[$field]) {
                return 'stock_adjusted';
            }
        }

        return 'updated';
    }

    protected function guessModule(Model $model): string
    {
        $class = class_basename($model);

        return match ($class) {
            'Product' => 'products',
            'Category' => 'categories',
            'Customer' => 'customers',
            'Supplier' => 'suppliers',
            'Sale' => 'sales',
            'PurchaseOrder' => 'purchases',
            'Invoice' => 'invoices',
            'User' => 'settings',
            'Role' => 'permissions',
            'Permission' => 'permissions',
            'CashRegister' => 'cash_register',
            default => str($class)->plural()->lower()->value(),
        };
    }

    protected function sanitize(?array $values): ?array
    {
        if ($values === null) return null;

        $sensitive = ['password', 'password_confirmation', 'remember_token', 'two_factor_secret'];

        return array_diff_key($values, array_flip($sensitive));
    }

    public static function guessEntityType(string $module): ?string
    {
        return match ($module) {
            'products' => 'App\Models\Product',
            'categories' => 'App\Models\Category',
            'customers' => 'App\Models\Customer',
            'suppliers' => 'App\Models\Supplier',
            'sales' => 'App\Models\Sale',
            'purchases' => 'App\Models\PurchaseOrder',
            'cash_register' => 'App\Models\CashRegister',
            default => null,
        };
    }
}
