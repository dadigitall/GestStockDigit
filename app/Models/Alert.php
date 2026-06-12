<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'severity',
        'title',
        'message',
        'action_url',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser($query, ?int $userId, int $companyId)
    {
        return $query->where('company_id', $companyId)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            });
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public static function severityColor(string $severity): string
    {
        return match ($severity) {
            'danger' => 'red',
            'warning' => 'amber',
            default => 'blue',
        };
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'stock_out' => 'Rupture de stock',
            'low_stock' => 'Stock bas',
            'near_expiry' => 'Expiration proche',
            'unpaid_invoice' => 'Facture impayée',
            'credit_exceeded' => 'Crédit client dépassé',
            'late_order' => 'Commande fournisseur en retard',
            'transfer_pending' => 'Transfert à valider',
            'requisition_received' => 'Demande d\'approvisionnement',
            'cash_register_open' => 'Caisse non clôturée',
            'inventory_discrepancy' => 'Écart d\'inventaire',
            'suspicious_login' => 'Connexion suspecte',
            'sensitive_action' => 'Action sensible',
            default => $type,
        };
    }
}
