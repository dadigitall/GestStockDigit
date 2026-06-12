<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'user_name',
        'action',
        'module',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'result',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForUser($query, ?int $userId, int $companyId)
    {
        return $query->where('company_id', $companyId)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            });
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public static function actionsList(): array
    {
        return [
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'failed_login' => 'Échec connexion',
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'restored' => 'Restauration',
            'price_changed' => 'Changement de prix',
            'stock_adjusted' => 'Ajustement de stock',
            'exported' => 'Export',
            'closed' => 'Clôture',
            'permission_changed' => 'Changement permissions',
            'cancelled' => 'Annulation',
            'validated' => 'Validation',
        ];
    }

    public static function modulesList(): array
    {
        return [
            'auth' => 'Authentification',
            'products' => 'Produits',
            'categories' => 'Catégories',
            'customers' => 'Clients',
            'suppliers' => 'Fournisseurs',
            'stock' => 'Stock',
            'sales' => 'Ventes',
            'purchases' => 'Achats',
            'invoices' => 'Factures',
            'permissions' => 'Permissions',
            'cash_register' => 'Caisse',
            'exports' => 'Exports',
            'settings' => 'Paramètres',
        ];
    }
}
