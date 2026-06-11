<?php

namespace App\Livewire\Pages\Settings;

use App\Models\Company;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public Company $company;

    public string $tab = 'general';

    // General
    public $name;

    public $legal_name;

    public $tax_number;

    public $registration_number;

    public $address;

    public $phone;

    public $email;

    public $website;

    public $currency;

    public $timezone;

    public $date_format;

    public $locale;

    public $logo;

    public $tempLogo;

    // Invoicing
    public $invoice_prefix;

    public $sale_prefix;

    public $purchase_prefix;

    public $delivery_prefix;

    public $quotation_prefix;

    public $credit_note_prefix;

    public $transfer_prefix;

    public $invoice_footer;

    public $invoice_terms;

    public $ticket_footer;

    // Stock & Finance
    public $default_tax_rate;

    public $discount_max_rate;

    public $credit_limit_default;

    public $alert_threshold_global;

    public $enable_multi_currency;

    public $secondary_currency;

    public function mount()
    {
        $this->company = Company::current();

        $this->fill([
            'name' => $this->company->name,
            'legal_name' => $this->company->legal_name,
            'tax_number' => $this->company->tax_number,
            'registration_number' => $this->company->registration_number,
            'address' => $this->company->address,
            'phone' => $this->company->phone,
            'email' => $this->company->email,
            'website' => $this->company->website,
            'currency' => $this->company->currency,
            'timezone' => $this->company->timezone,
            'date_format' => $this->company->date_format,
            'locale' => $this->company->locale,
            'invoice_prefix' => $this->company->invoice_prefix,
            'sale_prefix' => $this->company->sale_prefix,
            'purchase_prefix' => $this->company->purchase_prefix,
            'delivery_prefix' => $this->company->delivery_prefix,
            'quotation_prefix' => $this->company->quotation_prefix,
            'credit_note_prefix' => $this->company->credit_note_prefix,
            'transfer_prefix' => $this->company->transfer_prefix,
            'invoice_footer' => $this->company->invoice_footer,
            'invoice_terms' => $this->company->invoice_terms,
            'ticket_footer' => $this->company->ticket_footer,
            'default_tax_rate' => $this->company->default_tax_rate,
            'discount_max_rate' => $this->company->discount_max_rate,
            'credit_limit_default' => $this->company->credit_limit_default,
            'alert_threshold_global' => $this->company->alert_threshold_global,
            'enable_multi_currency' => $this->company->enable_multi_currency,
            'secondary_currency' => $this->company->secondary_currency,
        ]);
    }

    public function switchTab($tab)
    {
        $this->tab = $tab;
    }

    public function saveGeneral()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'currency' => 'required|string|size:3',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:20',
            'locale' => 'required|string|max:10',
            'tempLogo' => 'nullable|image|max:2048',
        ]);

        if ($this->tempLogo) {
            $this->company->logo = $this->tempLogo->store('logos', 'public');
        }

        $this->company->update([
            'name' => $this->name,
            'legal_name' => $this->legal_name,
            'tax_number' => $this->tax_number,
            'registration_number' => $this->registration_number,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'address' => $this->address,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'date_format' => $this->date_format,
            'locale' => $this->locale,
            'logo' => $this->company->logo,
        ]);

        session()->flash('success', 'Paramètres généraux enregistrés.');
    }

    public function saveInvoicing()
    {
        $this->validate([
            'invoice_prefix' => 'required|string|max:10',
            'sale_prefix' => 'required|string|max:10',
            'purchase_prefix' => 'required|string|max:10',
            'delivery_prefix' => 'required|string|max:10',
            'quotation_prefix' => 'required|string|max:10',
            'credit_note_prefix' => 'required|string|max:10',
            'transfer_prefix' => 'required|string|max:10',
            'invoice_footer' => 'nullable|string|max:2000',
            'invoice_terms' => 'nullable|string|max:5000',
            'ticket_footer' => 'nullable|string|max:1000',
        ]);

        $this->company->update([
            'invoice_prefix' => strtoupper($this->invoice_prefix),
            'sale_prefix' => strtoupper($this->sale_prefix),
            'purchase_prefix' => strtoupper($this->purchase_prefix),
            'delivery_prefix' => strtoupper($this->delivery_prefix),
            'quotation_prefix' => strtoupper($this->quotation_prefix),
            'credit_note_prefix' => strtoupper($this->credit_note_prefix),
            'transfer_prefix' => strtoupper($this->transfer_prefix),
            'invoice_footer' => $this->invoice_footer,
            'invoice_terms' => $this->invoice_terms,
            'ticket_footer' => $this->ticket_footer,
        ]);

        session()->flash('success', 'Paramètres de facturation enregistrés.');
    }

    public function saveStockFinance()
    {
        $this->validate([
            'default_tax_rate' => 'required|numeric|min:0|max:100',
            'discount_max_rate' => 'required|numeric|min:0|max:100',
            'credit_limit_default' => 'required|numeric|min:0',
            'alert_threshold_global' => 'nullable|integer|min:0',
            'enable_multi_currency' => 'boolean',
            'secondary_currency' => 'nullable|string|size:3',
        ]);

        $this->company->update([
            'default_tax_rate' => $this->default_tax_rate,
            'discount_max_rate' => $this->discount_max_rate,
            'credit_limit_default' => $this->credit_limit_default,
            'alert_threshold_global' => $this->alert_threshold_global,
            'enable_multi_currency' => (bool) $this->enable_multi_currency,
            'secondary_currency' => $this->secondary_currency,
        ]);

        session()->flash('success', 'Paramètres stock & finances enregistrés.');
    }

    public function render()
    {
        return view('livewire.pages.settings.index')
            ->layout('layouts.app', ['header' => 'Paramètres']);
    }
}
