<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerCategory;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\CustomerPayment;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\DocumentTemplate;
use App\Models\GiftCard;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Lot;
use App\Models\PaymentSchedule;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SerialNumber;
use App\Models\StockLoss;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\SupplierCreditNote;
use App\Models\SupplierEvaluation;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    private int $companyId;
    private array $storeIds = [];
    private array $userIds = [];
    private array $categoryIds = [];
    private array $supplierIds = [];
    private array $productIds = [];
    private array $customerIds = [];
    private array $unitIds = [];

    private ?int $catDetailId = null;
    private ?int $catGrosId = null;
    private ?int $catVipId = null;
    private ?int $catRevendeurId = null;
    private ?int $catEntrepriseId = null;
    private ?int $catCreditId = null;

    public function run(): void
    {
        $this->companyId = \App\Models\Company::value('id');

        if (!$this->companyId) {
            $this->command->error('Aucune entreprise trouvée. Exécute d\'abord DatabaseSeeder.');
            return;
        }

        // Force company currency to XOF
        \App\Models\Company::where('id', $this->companyId)->update(['currency' => 'XOF']);

        $this->loadExistingData();
        $this->seedStores();
        $this->seedUsers();
        $this->seedCategories();
        $this->seedSuppliers();
        $this->seedUnits();
        $this->seedUnitConversions();
        $this->seedProducts();
        $this->seedProductStore();
        $this->seedProductVariants();
        $this->seedCustomers();
        $this->seedPriceTiers();
        $this->seedPromotions();
        $this->seedCoupons();
        $this->seedPurchaseRequisitions();
        $this->seedPurchaseOrders();
        $this->seedGoodsReceipts();
        $this->seedStockMovements();
        $this->seedLocations();
        $this->seedLots();
        $this->seedQuotations();
        $this->seedCustomerOrders();
        $this->seedSales();
        $this->seedInvoices();
        $this->seedDeliveryNotes();
        $this->seedTransfers();
        $this->seedCustomerReturns();
        $this->seedSupplierReturns();
        $this->seedCustomerPayments();
        $this->seedInventory();
        $this->seedStockLosses();
        $this->seedCashRegisters();
        $this->seedBundles();
        $this->seedGiftCards();
        $this->seedAlerts();
        $this->seedDocumentTemplates();
        $this->seedSupplierEvaluations();
        $this->seedSerialNumbers();

        $this->command->info('✅ Données de démonstration insérées avec succès !');
    }

    private function loadExistingData(): void
    {
        $this->storeIds = Store::pluck('id', 'code')->toArray();
        $this->userIds = User::pluck('id', 'email')->toArray();
        $this->categoryIds = Category::pluck('id', 'slug')->toArray();
        $this->supplierIds = Supplier::pluck('id', 'name')->toArray();
        $this->customerIds = Customer::pluck('id', 'name')->toArray();
        $this->productIds = Product::pluck('id', 'reference')->toArray();
        $this->unitIds = Unit::pluck('id', 'slug')->toArray();

        // Customer categories
        $this->catDetailId = CustomerCategory::where('slug', 'detail')->value('id');
        $this->catGrosId = CustomerCategory::where('slug', 'gros')->value('id');
        $this->catVipId = CustomerCategory::where('slug', 'vip')->value('id');
        $this->catRevendeurId = CustomerCategory::where('slug', 'revendeur')->value('id');
        $this->catEntrepriseId = CustomerCategory::where('slug', 'entreprise')->value('id');
        $this->catCreditId = CustomerCategory::where('slug', 'credit')->value('id');
    }

    // ─── STORES ───────────────────────────────
    private function seedStores(): void
    {
        $stores = [
            ['name' => 'Boutique Principale — Yopougon', 'code' => 'BP-001', 'type' => 'boutique', 'address' => 'Yopougon Niangon, Abidjan', 'phone' => '+225 01 01 01 01', 'allows_stock' => true, 'allows_sales' => true, 'allows_cash_register' => true],
            ['name' => 'Point de Vente — Plateau', 'code' => 'PV-002', 'type' => 'point_de_vente', 'address' => 'Plateau, Avenue Noguès, Abidjan', 'phone' => '+225 01 02 02 02', 'allows_stock' => true, 'allows_sales' => true, 'allows_cash_register' => true],
            ['name' => 'Entrepôt — Km 15', 'code' => 'EW-001', 'type' => 'entrepot', 'address' => 'Km 15, Route d\'Alépé, Abidjan', 'phone' => '+225 01 03 03 03', 'allows_stock' => true, 'allows_sales' => false, 'allows_cash_register' => false],
        ];

        foreach ($stores as $s) {
            $store = Store::firstOrCreate(
                ['company_id' => $this->companyId, 'code' => $s['code']],
                array_merge($s, ['company_id' => $this->companyId, 'is_active' => true])
            );
            $this->storeIds[$store->code] = $store->id;
        }
    }

    // ─── USERS ────────────────────────────────
    private function seedUsers(): void
    {
        if (isset($this->userIds['admin@geststock.com'])) {
            // Make sure existing admin has proper store
            $admin = User::where('email', 'admin@geststock.com')->first();
            $admin->update(['store_id' => $this->storeIds['BP-001']]);
        }

        $users = [
            ['name' => 'Kouamé Jean', 'email' => 'manager@geststock.com', 'first_name' => 'Jean', 'last_name' => 'Kouamé', 'store_code' => 'BP-001', 'role' => 'Store Manager'],
            ['name' => 'Aminata Diallo', 'email' => 'caisse@geststock.com', 'first_name' => 'Aminata', 'last_name' => 'Diallo', 'store_code' => 'BP-001', 'role' => 'Cashier'],
            ['name' => 'Mamadou Traoré', 'email' => 'vendeur@geststock.com', 'first_name' => 'Mamadou', 'last_name' => 'Traoré', 'store_code' => 'PV-002', 'role' => 'Salesperson'],
            ['name' => 'Fatoumata Koné', 'email' => 'stock@geststock.com', 'first_name' => 'Fatoumata', 'last_name' => 'Koné', 'store_code' => 'EW-001', 'role' => 'Stock Manager'],
            ['name' => 'Marie-Esther Kouassi', 'email' => 'compta@geststock.com', 'first_name' => 'Marie-Esther', 'last_name' => 'Kouassi', 'store_code' => 'BP-001', 'role' => 'Accountant'],
            ['name' => 'Aboubacar Ouattara', 'email' => 'entrepot@geststock.com', 'first_name' => 'Aboubacar', 'last_name' => 'Ouattara', 'store_code' => 'EW-001', 'role' => 'Warehouse Manager'],
        ];

        foreach ($users as $u) {
            if (isset($this->userIds[$u['email']])) continue;
            $user = User::factory()->create([
                'company_id' => $this->companyId,
                'store_id' => $this->storeIds[$u['store_code']],
                'name' => $u['name'],
                'email' => $u['email'],
                'first_name' => $u['first_name'],
                'last_name' => $u['last_name'],
                'phone' => '+225 ' . fake()->unique()->numerify('## ## ## ##'),
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
            $user->assignRole($u['role']);
            $this->userIds[$u['email']] = $user->id;
        }
    }

    // ─── CATEGORIES ──────────────────────────
    private function seedCategories(): void
    {
        if (!empty($this->categoryIds)) return;

        $cats = [
            ['name' => 'Électronique & Multimédia', 'slug' => 'electronique', 'color' => '#6366f1', 'margin_rate' => 25, 'min_margin' => 15],
            ['name' => 'Informatique & Périphériques', 'slug' => 'informatique', 'color' => '#2563eb', 'margin_rate' => 20, 'min_margin' => 12],
            ['name' => 'Téléphonie & Accessoires', 'slug' => 'telephonie', 'color' => '#059669', 'margin_rate' => 30, 'min_margin' => 18],
            ['name' => 'Vêtements & Accessoires Mode', 'slug' => 'vetements', 'color' => '#d97706', 'margin_rate' => 50, 'min_margin' => 30],
            ['name' => 'Alimentation & Boissons', 'slug' => 'alimentation', 'color' => '#dc2626', 'margin_rate' => 35, 'min_margin' => 20],
            ['name' => 'Produits d\'Entretien', 'slug' => 'entretien', 'color' => '#0891b2', 'margin_rate' => 40, 'min_margin' => 25],
            ['name' => 'Fournitures de Bureau', 'slug' => 'bureau', 'color' => '#7c3aed', 'margin_rate' => 45, 'min_margin' => 28],
            ['name' => 'Quincaillerie & Bricolage', 'slug' => 'quincaillerie', 'color' => '#78716c', 'margin_rate' => 35, 'min_margin' => 20],
        ];

        foreach ($cats as $c) {
            $cat = Category::create(array_merge($c, ['company_id' => $this->companyId, 'is_active' => true]));
            $this->categoryIds[$cat->slug] = $cat->id;
        }
    }

    // ─── SUPPLIERS ───────────────────────────
    private function seedSuppliers(): void
    {
        if (!empty($this->supplierIds)) return;

        $suppliers = [
            ['name' => 'AfriTech Distribution SARL', 'type' => 'grossiste', 'contact_name' => 'M. Diallo', 'phone' => '+225 05 00 00 01', 'email' => 'contact@afritech.ci', 'address' => 'Zone Industrielle, Yopougon', 'payment_terms' => '30 jours', 'delivery_delay_days' => 5, 'currency' => 'XOF'],
            ['name' => 'Global Import CI', 'type' => 'importateur', 'contact_name' => 'Mme Bamba', 'phone' => '+225 05 00 00 02', 'email' => 'info@globalimport.ci', 'address' => 'Port Autonome, Abidjan', 'payment_terms' => '45 jours', 'delivery_delay_days' => 14, 'currency' => 'XOF'],
            ['name' => 'Nouvelle Mode Africaine', 'type' => 'fabricant', 'contact_name' => 'M. Konaté', 'phone' => '+225 05 00 00 03', 'email' => 'ventes@nouvellemode.ci', 'address' => 'Grand-Marché, Treichville', 'payment_terms' => '15 jours', 'delivery_delay_days' => 3, 'currency' => 'XOF'],
            ['name' => 'Distri Alim SA', 'type' => 'distributeur', 'contact_name' => 'Mme Yao', 'phone' => '+225 05 00 00 04', 'email' => 'commandes@distrialim.ci', 'address' => 'Adjamé, Abidjan', 'payment_terms' => '30 jours', 'delivery_delay_days' => 2, 'currency' => 'XOF'],
            ['name' => 'Pro Bureau Services', 'type' => 'grossiste', 'contact_name' => 'M. N\'Guessan', 'phone' => '+225 05 00 00 05', 'email' => 'info@probureau.ci', 'address' => 'Cocody, Abidjan', 'payment_terms' => '30 jours', 'delivery_delay_days' => 4, 'currency' => 'XOF'],
        ];

        foreach ($suppliers as $s) {
            $supplier = Supplier::create(array_merge($s, ['company_id' => $this->companyId, 'is_active' => true]));
            $this->supplierIds[$supplier->name] = $supplier->id;
        }
    }

    // ─── UNITS (use existing + add if needed) ──
    private function seedUnits(): void
    {
        // UnitSeeder already creates units, this is a fallback
        if (!empty($this->unitIds)) return;

        $units = [
            ['name' => 'Pièce', 'slug' => 'piece', 'base_unit' => true, 'type' => 'standard'],
            ['name' => 'Carton', 'slug' => 'carton', 'base_unit' => false, 'type' => 'standard'],
            ['name' => 'Paquet', 'slug' => 'paquet', 'base_unit' => false, 'type' => 'standard'],
            ['name' => 'Kilogramme', 'slug' => 'kg', 'base_unit' => true, 'type' => 'weight'],
            ['name' => 'Gramme', 'slug' => 'g', 'base_unit' => false, 'type' => 'weight'],
            ['name' => 'Litre', 'slug' => 'litre', 'base_unit' => true, 'type' => 'volume'],
            ['name' => 'Bouteille', 'slug' => 'bouteille', 'base_unit' => false, 'type' => 'standard'],
        ];

        foreach ($units as $u) {
            $unit = Unit::create(array_merge($u, ['company_id' => $this->companyId]));
            $this->unitIds[$unit->slug] = $unit->id;
        }
    }

    private function seedUnitConversions(): void
    {
        if (UnitConversion::where('company_id', $this->companyId)->exists()) return;

        $conversions = [
            ['from' => 'kg', 'to' => 'g', 'factor' => 1000],
            ['from' => 'g', 'to' => 'kg', 'factor' => 0.001],
            ['from' => 'carton', 'to' => 'piece', 'factor' => 12],
            ['from' => 'piece', 'to' => 'carton', 'factor' => 1 / 12],
        ];

        foreach ($conversions as $c) {
            UnitConversion::create([
                'company_id' => $this->companyId,
                'from_unit_id' => $this->unitIds[$c['from']],
                'to_unit_id' => $this->unitIds[$c['to']],
                'factor' => $c['factor'],
            ]);
        }
    }

    // ─── PRODUCTS ─────────────────────────────
    private function seedProducts(): void
    {
        if (!empty($this->productIds)) return;

        $products = [
            // Électronique
            ['name' => 'Téléviseur LED 43" Hisense', 'ref' => 'TV-001', 'cat' => 'electronique', 'sup' => 'AfriTech Distribution SARL', 'purchase' => 180000, 'sale' => 245000, 'wholesale' => 220000, 'min' => 3, 'stock' => 12],
            ['name' => 'Casque Audio Bluetooth JBL', 'ref' => 'CAS-001', 'cat' => 'electronique', 'sup' => 'AfriTech Distribution SARL', 'purchase' => 15000, 'sale' => 25000, 'wholesale' => 22000, 'min' => 10, 'stock' => 45],
            ['name' => 'Enceinte Portable Sony SRS-XB13', 'ref' => 'ENC-001', 'cat' => 'electronique', 'sup' => 'AfriTech Distribution SARL', 'purchase' => 22000, 'sale' => 35000, 'wholesale' => 32000, 'min' => 5, 'stock' => 28],

            // Informatique
            ['name' => 'Clavier Mécanique Logitech G413', 'ref' => 'CLV-001', 'cat' => 'informatique', 'sup' => 'AfriTech Distribution SARL', 'purchase' => 35000, 'sale' => 55000, 'wholesale' => 50000, 'min' => 5, 'stock' => 20],
            ['name' => 'Souris Sans Fil Logitech M720', 'ref' => 'SFS-001', 'cat' => 'informatique', 'sup' => 'AfriTech Distribution SARL', 'purchase' => 12000, 'sale' => 20000, 'wholesale' => 18000, 'min' => 10, 'stock' => 35],
            ['name' => 'Disque Dur Externe 1To Seagate', 'ref' => 'DDE-001', 'cat' => 'informatique', 'sup' => 'Global Import CI', 'purchase' => 28000, 'sale' => 42000, 'wholesale' => 38000, 'min' => 5, 'stock' => 15],

            // Téléphonie
            ['name' => 'Chargeur Rapide USB-C 65W Anker', 'ref' => 'CHG-001', 'cat' => 'telephonie', 'sup' => 'Global Import CI', 'purchase' => 8000, 'sale' => 15000, 'wholesale' => 13000, 'min' => 15, 'stock' => 60],
            ['name' => 'Coque iPhone 14 Pro Silicone', 'ref' => 'COQ-001', 'cat' => 'telephonie', 'sup' => 'Global Import CI', 'purchase' => 3000, 'sale' => 6500, 'wholesale' => 5500, 'min' => 20, 'stock' => 80],
            ['name' => 'Câble USB-C vers Lightning 2m', 'ref' => 'CBL-001', 'cat' => 'telephonie', 'sup' => 'Global Import CI', 'purchase' => 2500, 'sale' => 5000, 'wholesale' => 4500, 'min' => 30, 'stock' => 120],

            // Vêtements
            ['name' => 'Bazin Riche Homme — Couleur Bleu Nuit', 'ref' => 'BAZ-001', 'cat' => 'vetements', 'sup' => 'Nouvelle Mode Africaine', 'purchase' => 8500, 'sale' => 18000, 'wholesale' => 15000, 'min' => 20, 'stock' => 50, 'unit' => 'm'],
            ['name' => 'Chemise Homme Coton — Kenya', 'ref' => 'CHM-001', 'cat' => 'vetements', 'sup' => 'Nouvelle Mode Africaine', 'purchase' => 6500, 'sale' => 12500, 'wholesale' => 11000, 'min' => 15, 'stock' => 40],
            ['name' => 'Pagne Tissé Femme — Wax', 'ref' => 'PAG-001', 'cat' => 'vetements', 'sup' => 'Nouvelle Mode Africaine', 'purchase' => 4000, 'sale' => 9000, 'wholesale' => 7500, 'min' => 25, 'stock' => 70, 'unit' => 'm'],

            // Alimentation
            ['name' => 'Huile Végétale Pure 1L', 'ref' => 'HUI-001', 'cat' => 'alimentation', 'sup' => 'Distri Alim SA', 'purchase' => 1200, 'sale' => 2000, 'wholesale' => 1700, 'min' => 50, 'stock' => 200, 'unit' => 'bouteille', 'track_expiry' => true],
            ['name' => 'Riz Parfumé Long Grain 5kg', 'ref' => 'RIZ-001', 'cat' => 'alimentation', 'sup' => 'Distri Alim SA', 'purchase' => 2500, 'sale' => 4200, 'wholesale' => 3700, 'min' => 30, 'stock' => 85, 'track_lot' => true],
            ['name' => 'Boisson Jus d\'Ananas 1L', 'ref' => 'JUS-001', 'cat' => 'alimentation', 'sup' => 'Distri Alim SA', 'purchase' => 600, 'sale' => 1200, 'wholesale' => 1000, 'min' => 60, 'stock' => 150, 'unit' => 'bouteille', 'track_expiry' => true],

            // Entretien
            ['name' => 'Eau de Javel 2L', 'ref' => 'EDJ-001', 'cat' => 'entretien', 'sup' => 'Distri Alim SA', 'purchase' => 500, 'sale' => 1000, 'wholesale' => 850, 'min' => 40, 'stock' => 180],
            ['name' => 'Détergent Liquide 1L Lavande', 'ref' => 'DET-001', 'cat' => 'entretien', 'sup' => 'Distri Alim SA', 'purchase' => 800, 'sale' => 1500, 'wholesale' => 1300, 'min' => 30, 'stock' => 110],

            // Bureau
            ['name' => 'Ramette Papier A4 500 feuilles', 'ref' => 'PAP-001', 'cat' => 'bureau', 'sup' => 'Pro Bureau Services', 'purchase' => 3000, 'sale' => 5500, 'wholesale' => 4800, 'min' => 20, 'stock' => 60],
            ['name' => 'Stylo Bille Bleu — Boîte 50', 'ref' => 'STY-001', 'cat' => 'bureau', 'sup' => 'Pro Bureau Services', 'purchase' => 6000, 'sale' => 10000, 'wholesale' => 9000, 'min' => 10, 'stock' => 30],

            // Quincaillerie
            ['name' => 'Ampoule LED 12W E27', 'ref' => 'AMP-001', 'cat' => 'quincaillerie', 'sup' => 'Global Import CI', 'purchase' => 1200, 'sale' => 2500, 'wholesale' => 2000, 'min' => 40, 'stock' => 100],
            ['name' => 'Rouleau Adhésif Toile 48mm x 50m', 'ref' => 'RUB-001', 'cat' => 'quincaillerie', 'sup' => 'Global Import CI', 'purchase' => 800, 'sale' => 1800, 'wholesale' => 1500, 'min' => 20, 'stock' => 55],
        ];

        $now = now();
        foreach ($products as $p) {
            $supId = $this->supplierIds[$p['sup']] ?? null;
            $catId = $this->categoryIds[$p['cat']] ?? null;
            $unitSale = $p['unit'] ?? 'piece';
            $trackLot = $p['track_lot'] ?? false;
            $trackExpiry = $p['track_expiry'] ?? false;

            $product = Product::create([
                'company_id' => $this->companyId,
                'category_id' => $catId,
                'name' => $p['name'],
                'reference' => $p['ref'],
                'barcode' => '200' . str_pad((string) random_int(100000, 999999), 10, '0', STR_PAD_LEFT),
                'supplier_id' => $supId,
                'unit_sale' => $unitSale,
                'unit_purchase' => 'carton',
                'purchase_price' => $p['purchase'],
                'sale_price' => $p['sale'],
                'wholesale_price' => $p['wholesale'],
                'reseller_price' => (int) (($p['wholesale'] + $p['sale']) / 2),
                'tax_rate' => 18,
                'min_stock' => $p['min'],
                'max_stock' => $p['min'] * 10,
                'alert_threshold' => $p['min'],
                'stock_quantity' => $p['stock'],
                'is_active' => true,
                'is_sellable' => true,
                'is_stockable' => true,
                'track_lot' => $trackLot,
                'track_expiry' => $trackExpiry,
                'description' => 'Produit de qualité — ' . $p['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->productIds[$product->reference] = $product->id;
        }
    }

    // ─── PRODUCT-STORE PIVOT (stock) ──────────
    private function seedProductStore(): void
    {
        // Skip if pivot data already exists
        $existingCount = DB::table('product_store')->count();
        if ($existingCount > 0) return;

        $bps = $this->storeIds['BP-001'];
        $pvs = $this->storeIds['PV-002'];
        $ews = $this->storeIds['EW-001'];

        $assignments = [
            // Products with stock in Boutique Principale (15-30 items)
            ['ref' => 'TV-001', 'qty' => 5, 'stores' => [$bps, $pvs]],
            ['ref' => 'CAS-001', 'qty' => 20, 'stores' => [$bps, $pvs]],
            ['ref' => 'ENC-001', 'qty' => 12, 'stores' => [$bps, $pvs]],
            ['ref' => 'CLV-001', 'qty' => 8, 'stores' => [$bps, $pvs]],
            ['ref' => 'SFS-001', 'qty' => 15, 'stores' => [$bps, $pvs]],
            ['ref' => 'DDE-001', 'qty' => 6, 'stores' => [$bps, $pvs]],
            ['ref' => 'CHG-001', 'qty' => 30, 'stores' => [$bps, $pvs]],
            ['ref' => 'COQ-001', 'qty' => 40, 'stores' => [$bps, $pvs]],
            ['ref' => 'CBL-001', 'qty' => 60, 'stores' => [$bps, $pvs]],
            ['ref' => 'BAZ-001', 'qty' => 25, 'stores' => [$bps]],
            ['ref' => 'CHM-001', 'qty' => 20, 'stores' => [$bps, $pvs]],
            ['ref' => 'PAG-001', 'qty' => 35, 'stores' => [$bps]],
            ['ref' => 'HUI-001', 'qty' => 100, 'stores' => [$bps]],
            ['ref' => 'RIZ-001', 'qty' => 40, 'stores' => [$bps]],
            ['ref' => 'JUS-001', 'qty' => 70, 'stores' => [$bps]],
            ['ref' => 'EDJ-001', 'qty' => 90, 'stores' => [$bps]],
            ['ref' => 'DET-001', 'qty' => 55, 'stores' => [$bps]],
            ['ref' => 'PAP-001', 'qty' => 30, 'stores' => [$bps, $pvs]],
            ['ref' => 'STY-001', 'qty' => 15, 'stores' => [$bps, $pvs]],
            ['ref' => 'AMP-001', 'qty' => 50, 'stores' => [$bps]],
            ['ref' => 'RUB-001', 'qty' => 25, 'stores' => [$bps]],
        ];

        // Entrepôt receives carton quantities (12-24 per product in bulk)
        $warehouseProducts = array_keys($this->productIds);
        $now = now();

        foreach ($assignments as $a) {
            $pid = $this->productIds[$a['ref']] ?? null;
            if (!$pid) continue;

            foreach ($a['stores'] as $sid) {
                DB::table('product_store')->insert([
                    'product_id' => $pid,
                    'store_id' => $sid,
                    'stock_quantity' => $a['qty'],
                    'reserved_stock' => rand(1, 3),
                    'min_stock' => rand(3, 10),
                    'max_stock' => rand(50, 200),
                    'is_sellable' => true,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Entrepôt stock (in bulk)
        foreach ($warehouseProducts as $ref) {
            $pid = $this->productIds[$ref];
            DB::table('product_store')->insert([
                'product_id' => $pid,
                'store_id' => $ews,
                'stock_quantity' => rand(24, 96),
                'reserved_stock' => 0,
                'min_stock' => 12,
                'max_stock' => 500,
                'is_sellable' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    // ─── PRODUCT VARIANTS ────────────────────
    private function seedProductVariants(): void
    {
        // Only for t-shirt / chemise type products
        $chemise = Product::where('reference', 'CHM-001')->first();
        if ($chemise && $chemise->variants()->count() === 0) {
            $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
            foreach ($sizes as $size) {
                ProductVariant::create([
                    'product_id' => $chemise->id,
                    'name' => "Taille $size",
                    'sku' => "CHM-001-$size",
                    'attributes' => ['taille' => $size, 'couleur' => 'Blanc'],
                    'price' => [12500, 12500, 12500, 13500, 14500][array_search($size, $sizes)],
                    'purchase_price' => 6500,
                    'stock_quantity' => rand(3, 8),
                    'is_active' => true,
                ]);
            }
        }
    }

    // ─── CUSTOMERS ────────────────────────────
    private function seedCustomers(): void
    {
        if (!empty($this->customerIds)) return;

        $customers = [
            ['name' => 'Soro Ibrahima', 'type' => 'particular', 'cat' => 'detail', 'phone' => '+225 07 01 01 01', 'credit' => 0],
            ['name' => 'Touré Fatim', 'type' => 'particular', 'cat' => 'vip', 'phone' => '+225 07 02 02 02', 'credit' => 500000],
            ['name' => 'Établissements Koffi Frères', 'type' => 'professional', 'cat' => 'entreprise', 'phone' => '+225 07 03 03 03', 'credit' => 2000000],
            ['name' => 'Pharmacie du Centre SA', 'type' => 'professional', 'cat' => 'entreprise', 'phone' => '+225 07 04 04 04', 'credit' => 3000000],
            ['name' => 'Mamadou Coulibaly (Grossiste)', 'type' => 'wholesaler', 'cat' => 'gros', 'phone' => '+225 07 05 05 05', 'credit' => 1000000],
            ['name' => 'Awa Distribution', 'type' => 'reseller', 'cat' => 'revendeur', 'phone' => '+225 07 06 06 06', 'credit' => 500000],
            ['name' => 'Lycée Moderne de Koumassi', 'type' => 'professional', 'cat' => 'credit', 'phone' => '+225 07 07 07 07', 'credit' => 1500000],
            ['name' => 'Mairie de Yopougon', 'type' => 'professional', 'cat' => 'entreprise', 'phone' => '+225 07 08 08 08', 'credit' => 5000000],
        ];

        $catMapping = [
            'detail' => $this->catDetailId,
            'vip' => $this->catVipId,
            'entreprise' => $this->catEntrepriseId,
            'gros' => $this->catGrosId,
            'revendeur' => $this->catRevendeurId,
            'credit' => $this->catCreditId,
        ];

        foreach ($customers as $c) {
            $customer = Customer::create([
                'company_id' => $this->companyId,
                'customer_category_id' => $catMapping[$c['cat']],
                'name' => $c['name'],
                'type' => $c['type'],
                'phone' => $c['phone'],
                'email' => strtolower(str_replace([' ', '(', ')'], ['.', '', ''], $c['name'])) . '@email.ci',
                'address' => 'Abidjan, Côte d\'Ivoire',
                'credit_limit' => $c['credit'],
                'payment_terms' => $c['credit'] > 0 ? '30 jours' : 'comptant',
                'balance' => 0,
                'is_active' => true,
            ]);
            $this->customerIds[$customer->name] = $customer->id;
        }
    }

    // ─── PRICE TIERS ──────────────────────────
    private function seedPriceTiers(): void
    {
        if (PriceTier::where('company_id', $this->companyId)->exists()) return;

        $priceTiers = [
            // Remise 5% pour catégorie gros
            ['ccat' => 'gros', 'min' => 10, 'max' => null, 'label' => 'Prix gros 10+', 'pct' => 0.95],
            // Remise 10% pour catégorie entreprise
            ['ccat' => 'entreprise', 'min' => 5, 'max' => null, 'label' => 'Prix entreprise 5+', 'pct' => 0.90],
            // Remise 8% pour revendeur
            ['ccat' => 'revendeur', 'min' => 10, 'max' => null, 'label' => 'Prix revendeur 10+', 'pct' => 0.92],
            // Remise 15% pour VIP
            ['ccat' => 'vip', 'min' => 1, 'max' => null, 'label' => 'Prix VIP', 'pct' => 0.85],
        ];

        foreach ($this->productIds as $ref => $pid) {
            $product = Product::find($pid);
            if (!$product) continue;

            foreach ($priceTiers as $pt) {
                $ccatId = null;
                $slugs = ['gros' => $this->catGrosId, 'entreprise' => $this->catEntrepriseId, 'revendeur' => $this->catRevendeurId, 'vip' => $this->catVipId];
                $ccatId = $slugs[$pt['ccat']];

                PriceTier::create([
                    'company_id' => $this->companyId,
                    'product_id' => $pid,
                    'customer_category_id' => $ccatId,
                    'min_quantity' => $pt['min'],
                    'price' => round($product->sale_price * $pt['pct']),
                    'price_label' => $pt['label'],
                    'priority' => 1,
                    'is_active' => true,
                ]);
            }

            // Volume pricing for all: 20+ = 90% of wholesale
            PriceTier::create([
                'company_id' => $this->companyId,
                'product_id' => $pid,
                'min_quantity' => 20,
                'max_quantity' => null,
                'price' => round($product->wholesale_price * 0.9),
                'price_label' => 'Super volume 20+',
                'priority' => 0,
                'is_active' => true,
            ]);
        }
    }

    // ─── PROMOTIONS ───────────────────────────
    private function seedPromotions(): void
    {
        if (Promotion::where('company_id', $this->companyId)->exists()) return;

        $promo1 = Promotion::create([
            'company_id' => $this->companyId,
            'name' => 'Fête des Mères — Remise 15%',
            'type' => 'period',
            'description' => 'Remise de 15% sur tous les vêtements et accessoires',
            'discount_value' => 15,
            'discount_type' => 'percentage',
            'min_purchase' => 10000,
            'is_active' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addDays(25),
            'priority' => 1,
        ]);

        // Associer à la catégorie vêtements
        $vetementCat = Category::where('slug', 'vetements')->first();
        if ($vetementCat) {
            DB::table('promotion_category')->insert([
                'promotion_id' => $promo1->id,
                'category_id' => $vetementCat->id,
            ]);
        }

        $promo2 = Promotion::create([
            'company_id' => $this->companyId,
            'name' => 'Pack Bureau — 1 offert pour 3 achetés',
            'type' => 'buy_x_get_y',
            'description' => 'Achetez 4 ramettes, la 5e offerte !',
            'buy_quantity' => 4,
            'get_quantity' => 1,
            'min_quantity' => 4,
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(30),
            'priority' => 2,
        ]);

        $bureauCat = Category::where('slug', 'bureau')->first();
        if ($bureauCat) {
            DB::table('promotion_category')->insert([
                'promotion_id' => $promo2->id,
                'category_id' => $bureauCat->id,
            ]);
        }

        // Promo produit spécifique
        $ecran = Product::where('reference', 'TV-001')->first();
        if ($ecran) {
            Promotion::create([
                'company_id' => $this->companyId,
                'name' => 'Téléviseur à prix barré',
                'type' => 'barred_price',
                'description' => 'TV Hisense 43" à seulement 225 000 FCFA au lieu de 245 000',
                'discount_value' => 20000,
                'discount_type' => 'fixed',
                'is_active' => true,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(15),
                'priority' => 3,
            ]);
        }
    }

    // ─── COUPONS ──────────────────────────────
    private function seedCoupons(): void
    {
        if (Coupon::where('company_id', $this->companyId)->exists()) return;

        $promos = Promotion::where('company_id', $this->companyId)->get();

        Coupon::create([
            'company_id' => $this->companyId,
            'code' => 'BIENVENUE10',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 25000,
            'max_discount' => 20000,
            'usage_limit' => 100,
            'usage_per_customer' => 1,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(3),
        ]);

        Coupon::create([
            'company_id' => $this->companyId,
            'code' => 'FIDELITE20',
            'type' => 'percentage',
            'value' => 20,
            'min_order_amount' => 100000,
            'max_discount' => 50000,
            'usage_limit' => 50,
            'usage_per_customer' => 1,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(6),
        ]);

        Coupon::create([
            'company_id' => $this->companyId,
            'code' => 'LIVRAISON',
            'type' => 'fixed',
            'value' => 5000,
            'min_order_amount' => 50000,
            'usage_limit' => 200,
            'usage_per_customer' => 1,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    // ─── PURCHASE REQUISITIONS ────────────────
    private function seedPurchaseRequisitions(): void
    {
        if (PurchaseRequisition::where('company_id', $this->companyId)->exists()) return;
        $kone = $this->userIds['stock@geststock.com'] ?? $this->userIds['admin@geststock.com'];

        $req = PurchaseRequisition::create([
            'company_id' => $this->companyId,
            'store_id' => $this->storeIds['BP-001'],
            'requested_by' => $kone,
            'reference' => PurchaseRequisition::generateReference(),
            'priority' => 'high',
            'justification' => 'Stock épuisé — forte demande saisonnière',
            'desired_date' => now()->addDays(5),
            'status' => 'approved',
        ]);

        // Items: 3 products in short supply
        $shortProducts = ['CHG-001', 'CBL-001', 'HUI-001'];
        foreach ($shortProducts as $ref) {
            if (!isset($this->productIds[$ref])) continue;
            PurchaseRequisitionItem::create([
                'purchase_requisition_id' => $req->id,
                'product_id' => $this->productIds[$ref],
                'quantity' => 50,
            ]);
        }

        // Second requisition
        $req2 = PurchaseRequisition::create([
            'company_id' => $this->companyId,
            'store_id' => $this->storeIds['PV-002'],
            'requested_by' => $kone,
            'reference' => PurchaseRequisition::generateReference(),
            'priority' => 'medium',
            'justification' => 'Réapprovisionnement boutique Plateau',
            'desired_date' => now()->addDays(10),
            'status' => 'pending',
        ]);

        $otherProducts = ['SFS-001', 'PAP-001', 'CLV-001'];
        foreach ($otherProducts as $ref) {
            if (!isset($this->productIds[$ref])) continue;
            PurchaseRequisitionItem::create([
                'purchase_requisition_id' => $req2->id,
                'product_id' => $this->productIds[$ref],
                'quantity' => 15,
            ]);
        }
    }

    // ─── PURCHASE ORDERS ──────────────────────
    private function seedPurchaseOrders(): void
    {
        if (PurchaseOrder::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $afritech = $this->supplierIds['AfriTech Distribution SARL'];
        $globalImport = $this->supplierIds['Global Import CI'];
        $distri = $this->supplierIds['Distri Alim SA'];

        // PO 1: AfriTech - Electronics batch
        $po1 = PurchaseOrder::create([
            'company_id' => $this->companyId,
            'supplier_id' => $afritech,
            'store_id' => $this->storeIds['EW-001'],
            'user_id' => $adminId,
            'reference' => PurchaseOrder::generateReference(),
            'status' => 'completed',
            'payment_terms' => '30 jours',
            'delivery_date' => now()->subDays(10),
            'subtotal' => 0,
            'total' => 0,
        ]);

        $items1 = [
            ['ref' => 'TV-001', 'qty' => 10, 'price' => 180000],
            ['ref' => 'CAS-001', 'qty' => 30, 'price' => 15000],
            ['ref' => 'ENC-001', 'qty' => 20, 'price' => 22000],
        ];
        $subtotal1 = $this->createPOItems($po1, $items1);
        $po1->update(['subtotal' => $subtotal1, 'total' => $subtotal1 * 1.18]);

        // PO 2: Global Import - Accessories batch
        $po2 = PurchaseOrder::create([
            'company_id' => $this->companyId,
            'supplier_id' => $globalImport,
            'store_id' => $this->storeIds['EW-001'],
            'user_id' => $adminId,
            'reference' => PurchaseOrder::generateReference(),
            'status' => 'completed',
            'payment_terms' => '45 jours',
            'delivery_date' => now()->subDays(5),
            'subtotal' => 0,
            'total' => 0,
        ]);

        $items2 = [
            ['ref' => 'CHG-001', 'qty' => 100, 'price' => 8000],
            ['ref' => 'COQ-001', 'qty' => 60, 'price' => 3000],
            ['ref' => 'CBL-001', 'qty' => 200, 'price' => 2500],
            ['ref' => 'DDE-001', 'qty' => 15, 'price' => 28000],
        ];
        $subtotal2 = $this->createPOItems($po2, $items2);
        $po2->update(['subtotal' => $subtotal2, 'total' => $subtotal2 * 1.18]);

        // PO 3: Distri Alim - Food batch (in progress)
        $po3 = PurchaseOrder::create([
            'company_id' => $this->companyId,
            'supplier_id' => $distri,
            'store_id' => $this->storeIds['EW-001'],
            'user_id' => $adminId,
            'reference' => PurchaseOrder::generateReference(),
            'status' => 'sent',
            'payment_terms' => '30 jours',
            'delivery_date' => now()->addDays(5),
            'subtotal' => 0,
            'total' => 0,
        ]);

        $items3 = [
            ['ref' => 'HUI-001', 'qty' => 200, 'price' => 1200],
            ['ref' => 'RIZ-001', 'qty' => 100, 'price' => 2500],
            ['ref' => 'JUS-001', 'qty' => 300, 'price' => 600],
        ];
        $subtotal3 = $this->createPOItems($po3, $items3);
        $po3->update(['subtotal' => $subtotal3, 'total' => $subtotal3 * 1.18]);
    }

    private function createPOItems(PurchaseOrder $po, array $items): float
    {
        $subtotal = 0;
        foreach ($items as $it) {
            $tid = $this->productIds[$it['ref']] ?? null;
            if (!$tid) continue;
            $sub = $it['qty'] * $it['price'];
            $subtotal += $sub;
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $tid,
                'quantity' => $it['qty'],
                'unit_price' => $it['price'],
                'subtotal' => $sub,
                'tax_rate' => 18,
            ]);
        }
        return $subtotal;
    }

    // ─── GOODS RECEIPTS ──────────────────────
    private function seedGoodsReceipts(): void
    {
        if (GoodsReceipt::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $entrepot = $this->storeIds['EW-001'];
        $afritech = $this->supplierIds['AfriTech Distribution SARL'];
        $globalImport = $this->supplierIds['Global Import CI'];

        $po1 = PurchaseOrder::where('company_id', $this->companyId)->where('supplier_id', $afritech)->first();
        $po2 = PurchaseOrder::where('company_id', $this->companyId)->where('supplier_id', $globalImport)->first();

        if ($po1) {
            $gr1 = GoodsReceipt::create([
                'company_id' => $this->companyId,
                'purchase_order_id' => $po1->id,
                'supplier_id' => $afritech,
                'store_id' => $entrepot,
                'user_id' => $adminId,
                'reference' => GoodsReceipt::generateReference(),
                'status' => 'completed',
                'notes' => 'Réception complète — conforme au bon de commande',
            ]);

            foreach ($po1->items as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr1->id,
                    'purchase_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->quantity,
                    'quantity_accepted' => $item->quantity,
                    'quantity_rejected' => 0,
                    'unit_cost' => $item->unit_price,
                ]);
            }
        }

        if ($po2) {
            // Partial receipt for PO2 (some items delayed)
            $gr2 = GoodsReceipt::create([
                'company_id' => $this->companyId,
                'purchase_order_id' => $po2->id,
                'supplier_id' => $globalImport,
                'store_id' => $entrepot,
                'user_id' => $adminId,
                'reference' => GoodsReceipt::generateReference(),
                'status' => 'partial',
                'notes' => 'Réception partielle — chargeurs et câbles reçus, coques en attente',
            ]);

            foreach ($po2->items as $item) {
                $product = Product::find($item->product_id);
                $accepted = in_array($product?->reference, ['CHG-001', 'CBL-001', 'DDE-001']) ? $item->quantity : 0;
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $gr2->id,
                    'purchase_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_ordered' => $item->quantity,
                    'quantity_accepted' => $accepted,
                    'quantity_rejected' => 0,
                    'unit_cost' => $item->unit_price,
                ]);
            }
        }
    }

    // ─── STOCK MOVEMENTS ──────────────────────
    private function seedStockMovements(): void
    {
        if (StockMovement::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $bps = $this->storeIds['BP-001'];
        $ews = $this->storeIds['EW-001'];
        $now = now();

        // Initial stock entries from goods receipt
        $gr1 = GoodsReceipt::first();
        if ($gr1) {
            foreach ($gr1->items as $item) {
                StockMovement::create([
                    'company_id' => $this->companyId,
                    'product_id' => $item->product_id,
                    'store_id' => $ews,
                    'user_id' => $adminId,
                    'type' => 'purchase_in',
                    'quantity' => $item->quantity_accepted,
                    'unit' => 'piece',
                    'unit_cost' => $item->unit_cost,
                    'stock_before' => 0,
                    'stock_after' => $item->quantity_accepted,
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $gr1->id,
                    'status' => 'completed',
                    'notes' => 'Achat réceptionné',
                    'created_at' => $now->copy()->subDays(7),
                    'updated_at' => $now->copy()->subDays(7),
                ]);
            }
        }

        // Transfer from entrepot to boutique
        $transferProducts = ['TV-001', 'CAS-001', 'ENC-001', 'CHG-001'];
        foreach ($transferProducts as $ref) {
            $pid = $this->productIds[$ref] ?? null;
            if (!$pid) continue;

            // Get a unit cost from the product
            $product = Product::find($pid);
            StockMovement::create([
                'company_id' => $this->companyId,
                'product_id' => $pid,
                'store_id' => $bps,
                'source_store_id' => $ews,
                'destination_store_id' => $bps,
                'user_id' => $adminId,
                'type' => 'transfer_in',
                'quantity' => rand(3, 8),
                'unit' => 'piece',
                'unit_cost' => $product?->purchase_price ?? 0,
                'stock_before' => DB::table('product_store')->where('product_id', $pid)->where('store_id', $bps)->value('stock_quantity') ?? 0,
                'stock_after' => DB::table('product_store')->where('product_id', $pid)->where('store_id', $bps)->value('stock_quantity') ?? 0,
                'reference_type' => Transfer::class,
                'status' => 'completed',
                'notes' => 'Transfert depuis Entrepôt Km 15',
                'created_at' => $now->copy()->subDays(3),
                'updated_at' => $now->copy()->subDays(3),
            ]);
        }
    }

    // ─── LOCATIONS ────────────────────────────
    private function seedLocations(): void
    {
        if (Location::whereIn('store_id', $this->storeIds)->exists()) return;

        $bps = $this->storeIds['BP-001'];
        $ews = $this->storeIds['EW-001'];

        $bpsLocations = [
            ['name' => 'Rayon Électronique', 'code' => 'R-ELEC', 'type' => 'rayon'],
            ['name' => 'Rayon Mode', 'code' => 'R-MODE', 'type' => 'rayon'],
            ['name' => 'Rayon Alimentation', 'code' => 'R-ALIM', 'type' => 'rayon'],
            ['name' => 'Réserve Arrière', 'code' => 'RES-BP', 'type' => 'reserve'],
        ];

        foreach ($bpsLocations as $l) {
            Location::create([
                'store_id' => $bps,
                'name' => $l['name'],
                'code' => $l['code'],
                'type' => $l['type'],
                'is_active' => true,
            ]);
        }

        $ewsLocations = [
            ['name' => 'Zone A — Électronique', 'code' => 'ZA-ELEC', 'type' => 'allee'],
            ['name' => 'Zone B — Alimentaire', 'code' => 'ZB-ALIM', 'type' => 'allee'],
            ['name' => 'Zone C — Vêtements', 'code' => 'ZC-MODE', 'type' => 'allee'],
            ['name' => 'Zone D — Divers', 'code' => 'ZD-DIV', 'type' => 'allee'],
        ];

        foreach ($ewsLocations as $l) {
            Location::create([
                'store_id' => $ews,
                'name' => $l['name'],
                'code' => $l['code'],
                'type' => $l['type'],
                'is_active' => true,
            ]);
        }
    }

    // ─── LOTS ─────────────────────────────────
    private function seedLots(): void
    {
        if (Lot::where('company_id', $this->companyId)->exists()) return;

        $distri = $this->supplierIds['Distri Alim SA'];
        $now = now();

        $lotProducts = [
            ['ref' => 'HUI-001', 'qty' => 200],
            ['ref' => 'RIZ-001', 'qty' => 100],
            ['ref' => 'JUS-001', 'qty' => 300],
        ];

        foreach ($lotProducts as $lp) {
            $pid = $this->productIds[$lp['ref']] ?? null;
            if (!$pid) continue;

            Lot::create([
                'company_id' => $this->companyId,
                'product_id' => $pid,
                'supplier_id' => $distri,
                'lot_number' => 'LOT-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'manufacturing_date' => $now->copy()->subMonths(2),
                'expiry_date' => $now->copy()->addMonths(rand(3, 8)),
                'initial_quantity' => $lp['qty'],
                'remaining_quantity' => (int) ($lp['qty'] * 0.85),
                'status' => 'active',
            ]);
        }
    }

    // ─── QUOTATIONS ───────────────────────────
    private function seedQuotations(): void
    {
        if (Quotation::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $bps = $this->storeIds['BP-001'];

        // Devis pour la Mairie de Yopougon
        $mairieId = $this->customerIds['Mairie de Yopougon'] ?? null;
        if ($mairieId) {
            $dev1 = Quotation::create([
                'company_id' => $this->companyId,
                'customer_id' => $mairieId,
                'store_id' => $bps,
                'user_id' => $adminId,
                'reference' => 'DEV-2026-000001',
                'status' => 'sent',
                'validity_date' => now()->addDays(30),
                'commercial_terms' => 'Paiement sous 30 jours — Livraison Plateau',
                'sent_at' => now()->subDays(3),
                'subtotal' => 0,
                'total' => 0,
            ]);

            $devItems = [
                ['ref' => 'PAP-001', 'qty' => 20, 'price' => 5500],
                ['ref' => 'STY-001', 'qty' => 10, 'price' => 10000],
                ['ref' => 'CLV-001', 'qty' => 15, 'price' => 55000],
            ];
            $sub = $this->createQuotationItems($dev1, $devItems);
            $dev1->update(['subtotal' => $sub, 'total' => $sub * 1.18]);
        }

        // Devis pour Pharmacie du Centre
        $pharmaId = $this->customerIds['Pharmacie du Centre SA'] ?? null;
        if ($pharmaId) {
            $dev2 = Quotation::create([
                'company_id' => $this->companyId,
                'customer_id' => $pharmaId,
                'store_id' => $bps,
                'user_id' => $adminId,
                'reference' => 'DEV-2026-000002',
                'status' => 'draft',
                'validity_date' => now()->addDays(15),
                'commercial_terms' => 'Paiement comptant — Remise 5% pour règlement immédiat',
                'subtotal' => 0,
                'total' => 0,
            ]);

            $devItems2 = [
                ['ref' => 'DET-001', 'qty' => 30, 'price' => 1500],
                ['ref' => 'EDJ-001', 'qty' => 50, 'price' => 1000],
                ['ref' => 'AMP-001', 'qty' => 40, 'price' => 2500],
            ];
            $sub2 = $this->createQuotationItems($dev2, $devItems2);
            $dev2->update(['subtotal' => $sub2, 'total' => $sub2 * 1.18]);
        }
    }

    private function createQuotationItems(Quotation $q, array $items): float
    {
        $subtotal = 0;
        foreach ($items as $it) {
            $pid = $this->productIds[$it['ref']] ?? null;
            if (!$pid) continue;
            $product = Product::find($pid);
            $sub = $it['qty'] * $it['price'];
            $subtotal += $sub;
            QuotationItem::create([
                'quotation_id' => $q->id,
                'product_id' => $pid,
                'product_name' => $product?->name ?? $it['ref'],
                'product_reference' => $it['ref'],
                'unit' => 'piece',
                'quantity' => $it['qty'],
                'unit_price' => $it['price'],
                'subtotal' => $sub,
            ]);
        }
        return $subtotal;
    }

    // ─── CUSTOMER ORDERS ──────────────────────
    private function seedCustomerOrders(): void
    {
        if (CustomerOrder::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $bps = $this->storeIds['BP-001'];

        // Commande client - Koffi Frères
        $koffiId = $this->customerIds['Établissements Koffi Frères'] ?? null;
        if ($koffiId) {
            $co1 = CustomerOrder::create([
                'company_id' => $this->companyId,
                'customer_id' => $koffiId,
                'store_id' => $bps,
                'user_id' => $adminId,
                'reference' => 'BC-2026-000001',
                'status' => 'preparing',
                'order_date' => now()->subDays(2),
                'expected_delivery_date' => now()->addDays(10),
                'subtotal' => 0,
                'total' => 0,
            ]);

            $coItems = [
                ['ref' => 'CAS-001', 'qty' => 10, 'price' => 22000],
                ['ref' => 'DDE-001', 'qty' => 5, 'price' => 38000],
                ['ref' => 'CLV-001', 'qty' => 8, 'price' => 50000],
            ];
            $sub = $this->createCustomerOrderItems($co1, $coItems);
            $co1->update(['subtotal' => $sub, 'total' => $sub * 1.18]);
        }

        // Commande Awa Distribution
        $awaId = $this->customerIds['Awa Distribution'] ?? null;
        if ($awaId) {
            $co2 = CustomerOrder::create([
                'company_id' => $this->companyId,
                'customer_id' => $awaId,
                'store_id' => $bps,
                'user_id' => $adminId,
                'reference' => 'BC-2026-000002',
                'status' => 'completed',
                'order_date' => now()->subDays(7),
                'expected_delivery_date' => now()->subDays(2),
                'subtotal' => 0,
                'total' => 0,
            ]);

            $coItems2 = [
                ['ref' => 'PAG-001', 'qty' => 20, 'price' => 7500],
                ['ref' => 'BAZ-001', 'qty' => 15, 'price' => 15000],
                ['ref' => 'CHM-001', 'qty' => 12, 'price' => 11000],
            ];
            $sub2 = $this->createCustomerOrderItems($co2, $coItems2);
            $co2->update(['subtotal' => $sub2, 'total' => $sub2 * 1.18]);
        }
    }

    private function createCustomerOrderItems(CustomerOrder $co, array $items): float
    {
        $subtotal = 0;
        foreach ($items as $it) {
            $pid = $this->productIds[$it['ref']] ?? null;
            if (!$pid) continue;
            $product = Product::find($pid);
            $sub = $it['qty'] * $it['price'];
            $subtotal += $sub;
            CustomerOrderItem::create([
                'customer_order_id' => $co->id,
                'product_id' => $pid,
                'product_name' => $product?->name ?? $it['ref'],
                'product_reference' => $it['ref'],
                'unit' => 'piece',
                'quantity' => $it['qty'],
                'quantity_prepared' => $co->status === 'completed' ? $it['qty'] : (int) ($it['qty'] * 0.5),
                'quantity_delivered' => $co->status === 'completed' ? $it['qty'] : 0,
                'unit_price' => $it['price'],
                'subtotal' => $sub,
            ]);
        }
        return $subtotal;
    }

    // ─── SALES ────────────────────────────────
    private function seedSales(): void
    {
        if (Sale::where('company_id', $this->companyId)->exists()) return;

        $bpVendeur = $this->storeIds['BP-001'];
        $pvVendeur = $this->storeIds['PV-002'];
        $caisId = $this->userIds['caisse@geststock.com'] ?? $this->userIds['admin@geststock.com'];
        $venId = $this->userIds['vendeur@geststock.com'] ?? $this->userIds['admin@geststock.com'];
        $now = now();

        // Sale 1: Walk-in customer (particulier)
        $sale1 = Sale::create([
            'company_id' => $this->companyId,
            'store_id' => $bpVendeur,
            'user_id' => $caisId,
            'customer_id' => $this->customerIds['Soro Ibrahima'] ?? null,
            'reference' => Sale::generateReference(),
            'type' => 'retail',
            'status' => 'completed',
            'payment_method' => 'mobile_money',
            'sold_at' => $now->copy()->subDays(2),
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
        ]);

        $s1Items = [
            ['ref' => 'CHG-001', 'qty' => 2, 'price' => 15000],
            ['ref' => 'CBL-001', 'qty' => 3, 'price' => 5000],
            ['ref' => 'COQ-001', 'qty' => 1, 'price' => 6500],
        ];
        $s1Total = $this->createSaleItems($sale1, $s1Items);
        $sale1->update(['subtotal' => $s1Total, 'tax_amount' => round($s1Total * 0.18 / 1.18, 2), 'total' => $s1Total, 'paid_amount' => $s1Total]);

        // Sale 2: VIP customer with credit
        $sale2 = Sale::create([
            'company_id' => $this->companyId,
            'store_id' => $bpVendeur,
            'user_id' => $caisId,
            'customer_id' => $this->customerIds['Touré Fatim'] ?? null,
            'reference' => Sale::generateReference(),
            'type' => 'retail',
            'status' => 'completed',
            'payment_method' => 'cash',
            'sold_at' => $now->copy()->subDay(),
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
        ]);

        $s2Items = [
            ['ref' => 'ENC-001', 'qty' => 1, 'price' => 35000],
            ['ref' => 'CAS-001', 'qty' => 2, 'price' => 25000],
            ['ref' => 'CHM-001', 'qty' => 1, 'price' => 12500],
        ];
        $s2Total = $this->createSaleItems($sale2, $s2Items);
        $sale2->update(['subtotal' => $s2Total, 'tax_amount' => round($s2Total * 0.18 / 1.18, 2), 'total' => $s2Total, 'paid_amount' => $s2Total]);

        // Sale 3: Wholesale to Coulibaly
        $sale3 = Sale::create([
            'company_id' => $this->companyId,
            'store_id' => $bpVendeur,
            'user_id' => $caisId,
            'customer_id' => $this->customerIds['Mamadou Coulibaly (Grossiste)'] ?? null,
            'reference' => Sale::generateReference(),
            'type' => 'wholesale',
            'status' => 'completed',
            'payment_method' => 'credit',
            'sold_at' => $now->copy()->subDays(4),
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
        ]);

        $s3Items = [
            ['ref' => 'HUI-001', 'qty' => 50, 'price' => 1700],  // gros price
            ['ref' => 'RIZ-001', 'qty' => 25, 'price' => 3700],
            ['ref' => 'JUS-001', 'qty' => 60, 'price' => 1000],
        ];
        $s3Total = $this->createSaleItems($sale3, $s3Items);
        $sale3->update(['subtotal' => $s3Total, 'tax_amount' => round($s3Total * 0.18 / 1.18, 2), 'total' => $s3Total, 'paid_amount' => round($s3Total * 0.5)]);

        // Sale 4: Point de Vente Plateau walk-in
        $sale4 = Sale::create([
            'company_id' => $this->companyId,
            'store_id' => $pvVendeur,
            'user_id' => $venId,
            'reference' => Sale::generateReference(),
            'type' => 'retail',
            'status' => 'completed',
            'payment_method' => 'card',
            'sold_at' => $now->copy()->subDays(3),
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
        ]);

        $s4Items = [
            ['ref' => 'SFS-001', 'qty' => 2, 'price' => 20000],
            ['ref' => 'CBL-001', 'qty' => 1, 'price' => 5000],
        ];
        $s4Total = $this->createSaleItems($sale4, $s4Items);
        $sale4->update(['subtotal' => $s4Total, 'tax_amount' => round($s4Total * 0.18 / 1.18, 2), 'total' => $s4Total, 'paid_amount' => $s4Total]);

        // Sale 5: Établissements Koffi Frères (commercial B2B)
        $sale5 = Sale::create([
            'company_id' => $this->companyId,
            'store_id' => $bpVendeur,
            'user_id' => $caisId,
            'customer_id' => $this->customerIds['Établissements Koffi Frères'] ?? null,
            'reference' => Sale::generateReference(),
            'type' => 'wholesale',
            'status' => 'completed',
            'payment_method' => 'credit',
            'sold_at' => $now->copy()->subDays(6),
            'subtotal' => 0,
            'tax_amount' => 0,
            'total' => 0,
        ]);

        $s5Items = [
            ['ref' => 'TV-001', 'qty' => 2, 'price' => 220000],
            ['ref' => 'DDE-001', 'qty' => 3, 'price' => 38000],
            ['ref' => 'CLV-001', 'qty' => 5, 'price' => 50000],
        ];
        $s5Total = $this->createSaleItems($sale5, $s5Items);
        $sale5->update(['subtotal' => $s5Total, 'tax_amount' => round($s5Total * 0.18 / 1.18, 2), 'total' => $s5Total, 'paid_amount' => 0]);
    }

    private function createSaleItems(Sale $sale, array $items): float
    {
        $total = 0;
        foreach ($items as $it) {
            $pid = $this->productIds[$it['ref']] ?? null;
            if (!$pid) continue;
            $product = Product::find($pid);
            $sub = $it['qty'] * $it['price'];
            $total += $sub;
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $pid,
                'product_name' => $product?->name ?? $it['ref'],
                'product_reference' => $it['ref'],
                'unit' => 'piece',
                'quantity' => $it['qty'],
                'unit_price' => $it['price'],
                'subtotal' => $sub,
                'tax_rate' => 18,
            ]);
        }
        return $total;
    }

    // ─── INVOICES ─────────────────────────────
    private function seedInvoices(): void
    {
        if (Invoice::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $bps = $this->storeIds['BP-001'];
        $sales = Sale::where('company_id', $this->companyId)->where('type', 'wholesale')->get();

        foreach ($sales as $sale) {
            $inv = Invoice::create([
                'company_id' => $this->companyId,
                'customer_id' => $sale->customer_id,
                'store_id' => $sale->store_id,
                'user_id' => $adminId,
                'reference' => 'FAC-2026-' . str_pad($sale->id, 6, '0', STR_PAD_LEFT),
                'type' => 'invoice',
                'status' => $sale->paid_amount >= $sale->total ? 'paid' : 'unpaid',
                'sale_id' => $sale->id,
                'subtotal' => $sale->subtotal,
                'tax_amount' => $sale->tax_amount,
                'total' => $sale->total,
                'paid_amount' => $sale->paid_amount,
                'amount_due' => $sale->total - $sale->paid_amount,
                'issue_date' => $sale->sold_at ?? now()->subDays(5),
                'due_date' => $sale->payment_method === 'credit' ? now()->addDays(30) : $sale->sold_at ?? now(),
                'payment_terms' => $sale->payment_method === 'credit' ? '30 jours' : 'comptant',
            ]);

            // Copy sale items to invoice items
            foreach ($sale->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_reference' => $item->product_reference,
                    'unit' => $item->unit,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'tax_rate' => $item->tax_rate,
                ]);
            }
        }
    }

    // ─── DELIVERY NOTES ───────────────────────
    private function seedDeliveryNotes(): void
    {
        if (DeliveryNote::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];

        // Bon de livraison pour Awa Distribution
        $awaId = $this->customerIds['Awa Distribution'] ?? null;
        $co2 = CustomerOrder::where('customer_id', $awaId)->first();

        if ($awaId && $co2) {
            $dn = DeliveryNote::create([
                'company_id' => $this->companyId,
                'customer_id' => $awaId,
                'store_id' => $this->storeIds['BP-001'],
                'user_id' => $adminId,
                'reference' => 'BL-2026-000001',
                'status' => 'delivered',
                'source_type' => CustomerOrder::class,
                'source_id' => $co2->id,
                'delivery_date' => now()->subDays(2),
                'received_date' => now()->subDay(),
                'receiver_name' => 'Awa Konaté',
                'notes' => 'Livraison effectuée au magasin client — Bon état général',
            ]);

            foreach ($co2->items as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $dn->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'unit' => $item->unit,
                    'quantity_requested' => $item->quantity,
                    'quantity_delivered' => $item->quantity,
                ]);
            }
        }
    }

    // ─── TRANSFERS ────────────────────────────
    private function seedTransfers(): void
    {
        if (Transfer::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $managerId = $this->userIds['manager@geststock.com'];
        $koneId = $this->userIds['stock@geststock.com'];
        $bps = $this->storeIds['BP-001'];
        $ews = $this->storeIds['EW-001'];
        $now = now();

        // Transfer 1: Entrepôt -> Boutique (completed)
        $tr1 = Transfer::create([
            'company_id' => $this->companyId,
            'reference' => 'TR-2026-000001',
            'title' => 'Réassort Yopougon — Semaine 24',
            'source_store_id' => $ews,
            'destination_store_id' => $bps,
            'status' => 'fully_received',
            'requested_by' => $managerId,
            'approved_by' => $adminId,
            'shipped_by' => $koneId,
            'received_by' => $managerId,
            'requested_at' => $now->copy()->subDays(10),
            'approved_at' => $now->copy()->subDays(9),
            'shipped_at' => $now->copy()->subDays(8),
            'received_at' => $now->copy()->subDays(7),
            'notes' => 'Réapprovisionnement produits électroniques',
        ]);

        $tr1Items = [
            ['ref' => 'TV-001', 'qty' => 3],
            ['ref' => 'CAS-001', 'qty' => 10],
            ['ref' => 'ENC-001', 'qty' => 8],
        ];
        foreach ($tr1Items as $it) {
            $pid = $this->productIds[$it['ref']] ?? null;
            if (!$pid) continue;
            $product = Product::find($pid);
            TransferItem::create([
                'transfer_id' => $tr1->id,
                'product_id' => $pid,
                'quantity_requested' => $it['qty'],
                'quantity_shipped' => $it['qty'],
                'quantity_received' => $it['qty'],
                'unit_cost' => $product?->purchase_price ?? 0,
                'status' => 'completed',
            ]);
        }

        // Transfer 2: Entrepôt -> Plateau (pending)
        $tr2 = Transfer::create([
            'company_id' => $this->companyId,
            'reference' => 'TR-2026-000002',
            'title' => 'Réassort Plateau — Juillet',
            'source_store_id' => $ews,
            'destination_store_id' => $this->storeIds['PV-002'],
            'status' => 'shipped',
            'requested_by' => $managerId,
            'approved_by' => $adminId,
            'shipped_by' => $koneId,
            'requested_at' => $now->copy()->subDays(3),
            'approved_at' => $now->copy()->subDays(2),
            'shipped_at' => $now->copy()->subDay(),
            'notes' => 'Nouveaux arrivages',
        ]);

        $tr2Items = [
            ['ref' => 'SFS-001', 'qty' => 10],
            ['ref' => 'PAP-001', 'qty' => 15],
            ['ref' => 'CHG-001', 'qty' => 20],
        ];
        foreach ($tr2Items as $it) {
            $pid = $this->productIds[$it['ref']] ?? null;
            if (!$pid) continue;
            $product = Product::find($pid);
            TransferItem::create([
                'transfer_id' => $tr2->id,
                'product_id' => $pid,
                'quantity_requested' => $it['qty'],
                'quantity_shipped' => $it['qty'],
                'unit_cost' => $product?->purchase_price ?? 0,
                'status' => 'shipped',
            ]);
        }
    }

    // ─── CUSTOMER RETURNS ─────────────────────
    private function seedCustomerReturns(): void
    {
        if (CustomerReturn::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $bps = $this->storeIds['BP-001'];
        $soroId = $this->customerIds['Soro Ibrahima'] ?? null;
        $sale = Sale::where('customer_id', $soroId)->first();

        if ($soroId && $sale) {
            $ret = CustomerReturn::create([
                'company_id' => $this->companyId,
                'store_id' => $bps,
                'user_id' => $adminId,
                'customer_id' => $soroId,
                'sale_id' => $sale->id,
                'reference' => 'RCL-2026-000001',
                'return_type' => 'refund',
                'reason' => 'Produit défectueux',
                'reason_description' => 'Le chargeur rapide ne fonctionne pas correctement',
                'restock' => false,
                'refund_method' => 'mobile_money',
                'refund_amount' => 15000,
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now()->subDay(),
            ]);

            $chargeur = Product::where('reference', 'CHG-001')->first();
            if ($chargeur) {
                CustomerReturnItem::create([
                    'customer_return_id' => $ret->id,
                    'product_id' => $chargeur->id,
                    'quantity' => 1,
                    'unit_price' => 15000,
                    'total' => 15000,
                    'product_condition' => 'defective',
                    'restock' => false,
                    'refund_amount' => 15000,
                ]);
            }
        }
    }

    // ─── SUPPLIER RETURNS ─────────────────────
    private function seedSupplierReturns(): void
    {
        if (SupplierReturn::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $globalImport = $this->supplierIds['Global Import CI'];

        $gr = GoodsReceipt::whereHas('purchaseOrder', function ($q) use ($globalImport) {
            $q->where('supplier_id', $globalImport);
        })->first();

        if ($gr) {
            $sr = SupplierReturn::create([
                'company_id' => $this->companyId,
                'supplier_id' => $globalImport,
                'store_id' => $this->storeIds['EW-001'],
                'user_id' => $adminId,
                'purchase_order_id' => $gr->purchase_order_id,
                'goods_receipt_id' => $gr->id,
                'reference' => SupplierReturn::generateReference(),
                'reason_type' => 'defective',
                'return_type' => 'exchange',
                'status' => 'pending',
                'notes' => 'Coques iPhone défectueuses — retour pour échange',
            ]);

            $coque = Product::where('reference', 'COQ-001')->first();
            if ($coque) {
                SupplierReturnItem::create([
                    'supplier_return_id' => $sr->id,
                    'product_id' => $coque->id,
                    'quantity' => 5,
                    'unit_cost' => 3000,
                    'reason' => 'Fissures sur les bords — lot défectueux',
                ]);
            }
        }
    }

    // ─── CUSTOMER PAYMENTS ────────────────────
    private function seedCustomerPayments(): void
    {
        if (CustomerPayment::where('company_id', $this->companyId)->exists()) return;

        $coulibalyId = $this->customerIds['Mamadou Coulibaly (Grossiste)'] ?? null;
        $sale3 = Sale::where('customer_id', $coulibalyId)->first();

        if ($coulibalyId && $sale3) {
            // Paiement partiel
            $payment = CustomerPayment::create([
                'company_id' => $this->companyId,
                'customer_id' => $coulibalyId,
                'sale_id' => $sale3->id,
                'amount' => $sale3->paid_amount,
                'payment_date' => now()->subDays(4),
                'payment_method' => 'mobile_money',
                'reference' => 'PMT-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'notes' => 'Acompte 50% — vente en gros',
            ]);

            // Payment schedule for remaining balance
            PaymentSchedule::create([
                'company_id' => $this->companyId,
                'customer_id' => $coulibalyId,
                'sale_id' => $sale3->id,
                'due_date' => now()->addDays(26),
                'amount' => $sale3->total - $sale3->paid_amount,
                'paid_amount' => 0,
                'status' => 'pending',
                'notes' => 'Solde vente en gros — échéance 30 jours',
            ]);
        }

        // Payment schedule for Koffi Frères
        $koffiId = $this->customerIds['Établissements Koffi Frères'] ?? null;
        $sale5 = Sale::where('customer_id', $koffiId)->first();
        if ($koffiId && $sale5) {
            PaymentSchedule::create([
                'company_id' => $this->companyId,
                'customer_id' => $koffiId,
                'sale_id' => $sale5->id,
                'due_date' => now()->addDays(24),
                'amount' => $sale5->total,
                'paid_amount' => 0,
                'status' => 'pending',
                'notes' => 'Facture à 30 jours',
            ]);
        }

        // Payment schedule for the Lycée Moderne (simulate overdue)
        $lyceeId = $this->customerIds['Lycée Moderne de Koumassi'] ?? null;
        $anySale = Sale::where('company_id', $this->companyId)->first();
        if ($lyceeId && $anySale) {
            PaymentSchedule::create([
                'company_id' => $this->companyId,
                'customer_id' => $lyceeId,
                'sale_id' => $anySale->id,
                'due_date' => now()->subDays(5),
                'amount' => 250000,
                'paid_amount' => 0,
                'status' => 'overdue',
                'notes' => 'Facture fournitures scolaires — en retard',
            ]);
        }
    }

    // ─── INVENTORY ────────────────────────────
    private function seedInventory(): void
    {
        if (Inventory::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $koneId = $this->userIds['stock@geststock.com'];
        $bps = $this->storeIds['BP-001'];

        $inv = Inventory::create([
            'company_id' => $this->companyId,
            'reference' => 'INV-2026-001',
            'title' => 'Inventaire mensuel — Boutique Yopougon',
            'type' => 'partial',
            'status' => 'completed',
            'store_id' => $bps,
            'created_by' => $koneId,
            'validated_by' => $adminId,
            'started_at' => now()->subDays(15),
            'completed_at' => now()->subDays(13),
            'validated_at' => now()->subDays(12),
            'notes' => 'Inventaire des produits électroniques et alimentaires',
            'total_items' => 10,
            'total_discrepancies' => 1,
            'freeze_stock' => false,
        ]);

        // Inventory items for products in BP-001
        $invProducts = ['TV-001', 'CAS-001', 'ENC-001', 'CHG-001', 'HUI-001', 'RIZ-001', 'JUS-001', 'PAP-001', 'AMP-001', 'DET-001'];
        foreach ($invProducts as $ref) {
            $pid = $this->productIds[$ref] ?? null;
            if (!$pid) continue;

            $pivot = DB::table('product_store')
                ->where('product_id', $pid)
                ->where('store_id', $bps)
                ->first();

            $theoretical = $pivot?->stock_quantity ?? 0;
            $physical = $theoretical;

            // Simulate 1 discrepancy (TV stock off by 1)
            if ($ref === 'TV-001') {
                $physical = $theoretical - 1;
            }

            $discQty = $physical - $theoretical;
            $product = Product::find($pid);

            InventoryItem::create([
                'inventory_id' => $inv->id,
                'product_id' => $pid,
                'store_id' => $bps,
                'theoretical_quantity' => $theoretical,
                'physical_quantity' => $physical,
                'discrepancy_quantity' => $discQty,
                'discrepancy_value' => abs($discQty) * ($product?->purchase_price ?? 0),
                'unit_cost' => $product?->purchase_price ?? 0,
                'status' => 'counted',
                'decision' => $discQty !== 0 ? 'pending' : 'approved',
                'counted_by' => $koneId,
                'counted_at' => now()->subDays(14),
                'notes' => $discQty !== 0 ? 'Écart constaté' : 'Conforme',
            ]);
        }
    }

    // ─── STOCK LOSSES ─────────────────────────
    private function seedStockLosses(): void
    {
        if (StockLoss::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];
        $bps = $this->storeIds['BP-001'];

        // Perte par casse (bouteille d'huile cassée)
        $huile = Product::where('reference', 'HUI-001')->first();
        if ($huile) {
            StockLoss::create([
                'company_id' => $this->companyId,
                'store_id' => $bps,
                'user_id' => $adminId,
                'product_id' => $huile->id,
                'reference' => StockLoss::generateReference(),
                'loss_type' => 'breakage',
                'quantity' => 2,
                'unit_price' => $huile->purchase_price,
                'total_value' => $huile->purchase_price * 2,
                'reason' => 'Bouteilles cassées lors du déchargement',
                'status' => 'approved',
                'approved_by' => $adminId,
                'approved_at' => now()->subDays(5),
                'notes' => 'Déclaration de perte approuvée',
            ]);
        }

        // Perte par vol (casque audio)
        $casque = Product::where('reference', 'CAS-001')->first();
        if ($casque) {
            StockLoss::create([
                'company_id' => $this->companyId,
                'store_id' => $bps,
                'user_id' => $adminId,
                'product_id' => $casque->id,
                'reference' => StockLoss::generateReference(),
                'loss_type' => 'theft',
                'quantity' => 1,
                'unit_price' => $casque->purchase_price,
                'total_value' => $casque->purchase_price,
                'reason' => 'Vol constaté en rayon',
                'status' => 'pending',
                'notes' => 'En attente d\'approbation',
            ]);
        }
    }

    // ─── CASH REGISTERS ───────────────────────
    private function seedCashRegisters(): void
    {
        if (CashRegister::where('company_id', $this->companyId)->exists()) return;

        $bps = $this->storeIds['BP-001'];
        $caisId = $this->userIds['caisse@geststock.com'];
        $adminId = $this->userIds['admin@geststock.com'];

        // Closed register (yesterday)
        $cr1 = CashRegister::create([
            'company_id' => $this->companyId,
            'store_id' => $bps,
            'user_id' => $caisId,
            'name' => 'Caisse Principale — Yopougon',
            'code' => 'CSH-BP-001',
            'status' => 'closed',
            'initial_balance' => 50000,
            'current_balance' => 50000,
            'expected_balance' => 195000,
            'opened_at' => now()->subDays(2)->setTime(8, 0),
            'closed_at' => now()->subDays(2)->setTime(18, 0),
            'opened_by' => $caisId,
            'closed_by' => $caisId,
            'counted_amount' => 195500,
            'difference' => 500,
            'validated_by' => $adminId,
            'closing_note' => 'Fonds de caisse ok — écart de 500 FCFA positif',
        ]);

        // Cash movements for closed register
        CashMovement::create([
            'company_id' => $this->companyId,
            'cash_register_id' => $cr1->id,
            'store_id' => $bps,
            'user_id' => $caisId,
            'sourceable_type' => CashRegister::class,
            'sourceable_id' => $cr1->id,
            'type' => 'opening_balance',
            'direction' => 'in',
            'amount' => 50000,
            'payment_method' => 'cash',
            'description' => 'Fond de caisse initial',
            'movement_date' => now()->subDays(2)->setTime(8, 0),
        ]);

        // Cash sales
        CashMovement::create([
            'company_id' => $this->companyId,
            'cash_register_id' => $cr1->id,
            'store_id' => $bps,
            'user_id' => $caisId,
            'sourceable_type' => CashRegister::class,
            'sourceable_id' => $cr1->id,
            'type' => 'cash_sale',
            'direction' => 'in',
            'amount' => 97500,
            'payment_method' => 'cash',
            'reference' => Sale::where('store_id', $bps)->first()?->reference ?? 'VENTE',
            'description' => 'Ventes en espèces de la journée',
            'movement_date' => now()->subDays(2)->setTime(14, 0),
        ]);

        CashMovement::create([
            'company_id' => $this->companyId,
            'cash_register_id' => $cr1->id,
            'store_id' => $bps,
            'user_id' => $caisId,
            'sourceable_type' => CashRegister::class,
            'sourceable_id' => $cr1->id,
            'type' => 'cash_sale',
            'direction' => 'in',
            'amount' => 48000,
            'payment_method' => 'mobile_money',
            'description' => 'Ventes par Mobile Money (Orange Money)',
            'movement_date' => now()->subDays(2)->setTime(16, 0),
        ]);

        // Expense
        CashMovement::create([
            'company_id' => $this->companyId,
            'cash_register_id' => $cr1->id,
            'store_id' => $bps,
            'user_id' => $caisId,
            'sourceable_type' => CashRegister::class,
            'sourceable_id' => $cr1->id,
            'type' => 'internal_expense',
            'direction' => 'out',
            'amount' => 5000,
            'payment_method' => 'cash',
            'description' => 'Achat de fournitures de nettoyage',
            'movement_date' => now()->subDays(2)->setTime(15, 0),
        ]);

        // Open register (today) — use the date in the code to avoid unique constraint issues
        $todayCode = 'CSH-BP-' . now()->format('Ymd');
        $cr2 = CashRegister::firstOrCreate(
            ['code' => $todayCode],
            [
                'company_id' => $this->companyId,
                'store_id' => $bps,
                'user_id' => $caisId,
                'name' => 'Caisse Principale — Yopougon',
                'code' => $todayCode,
                'status' => 'open',
                'initial_balance' => 50000,
                'current_balance' => 50000,
                'expected_balance' => 50000,
                'opened_at' => now()->setTime(8, 0),
                'opened_by' => $caisId,
            ]
        );

        CashMovement::create([
            'company_id' => $this->companyId,
            'cash_register_id' => $cr2->id,
            'store_id' => $bps,
            'user_id' => $caisId,
            'sourceable_type' => CashRegister::class,
            'sourceable_id' => $cr2->id,
            'type' => 'opening_balance',
            'direction' => 'in',
            'amount' => 50000,
            'payment_method' => 'cash',
            'description' => 'Fond de caisse initial',
            'movement_date' => now()->setTime(8, 0),
        ]);

        // Associate cashier with register
        DB::table('cash_register_user')->insert([
            'cash_register_id' => $cr2->id,
            'user_id' => $caisId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ─── BUNDLES ──────────────────────────────
    private function seedBundles(): void
    {
        if (Bundle::where('company_id', $this->companyId)->exists()) return;

        // Pack Bureau
        $bundle = Bundle::create([
            'company_id' => $this->companyId,
            'name' => 'Pack Bureau Essentiel',
            'description' => '5 ramettes + 1 boîte stylos + 1 souris sans fil',
            'bundle_price' => 45000,
            'savings' => 8500,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(2),
        ]);

        $papier = Product::where('reference', 'PAP-001')->first();
        $stylo = Product::where('reference', 'STY-001')->first();
        $souris = Product::where('reference', 'SFS-001')->first();

        if ($papier) BundleItem::create(['bundle_id' => $bundle->id, 'product_id' => $papier->id, 'quantity' => 5]);
        if ($stylo) BundleItem::create(['bundle_id' => $bundle->id, 'product_id' => $stylo->id, 'quantity' => 1]);
        if ($souris) BundleItem::create(['bundle_id' => $bundle->id, 'product_id' => $souris->id, 'quantity' => 1]);

        // Pack Nettoyage
        $bundle2 = Bundle::create([
            'company_id' => $this->companyId,
            'name' => 'Pack Nettoyage Maison',
            'description' => '3L d\'eau de Javel + 2L de détergent lavande',
            'bundle_price' => 5000,
            'savings' => 1000,
            'is_active' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->addMonths(1),
        ]);

        $edj = Product::where('reference', 'EDJ-001')->first();
        $det = Product::where('reference', 'DET-001')->first();

        if ($edj) BundleItem::create(['bundle_id' => $bundle2->id, 'product_id' => $edj->id, 'quantity' => 3]);
        if ($det) BundleItem::create(['bundle_id' => $bundle2->id, 'product_id' => $det->id, 'quantity' => 2]);
    }

    // ─── GIFT CARDS ───────────────────────────
    private function seedGiftCards(): void
    {
        if (GiftCard::where('company_id', $this->companyId)->exists()) return;

        $touréId = $this->customerIds['Touré Fatim'] ?? null;

        GiftCard::create([
            'company_id' => $this->companyId,
            'code' => 'GIFT-' . strtoupper(substr(md5('card1'), 0, 8)),
            'initial_balance' => 50000,
            'balance' => 35000,
            'customer_id' => $touréId,
            'expires_at' => now()->addYear(),
            'status' => 'active',
            'notes' => 'Carte cadeau offerte à la cliente VIP',
        ]);

        GiftCard::create([
            'company_id' => $this->companyId,
            'code' => 'GIFT-' . strtoupper(substr(md5('card2'), 0, 8)),
            'initial_balance' => 25000,
            'balance' => 25000,
            'expires_at' => now()->addMonths(6),
            'status' => 'active',
            'notes' => 'Carte cadeau disponible à la vente',
        ]);
    }

    // ─── ALERTS ───────────────────────────────
    private function seedAlerts(): void
    {
        if (Alert::where('company_id', $this->companyId)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];

        $alerts = [
            ['type' => 'low_stock', 'severity' => 'warning', 'title' => 'Stock bas — Téléviseur LED', 'message' => 'Le stock du téléviseur Hisense 43" est à 2 unités (seuil d\'alerte: 3)', 'notifiable_type' => Product::class, 'notifiable_id' => Product::where('reference', 'TV-001')->first()?->id],
            ['type' => 'unpaid_invoice', 'severity' => 'danger', 'title' => 'Facture impayée — Koffi Frères', 'message' => 'La facture FAC-2026 de 729 000 FCFA est en attente de règlement', 'notifiable_type' => Customer::class, 'notifiable_id' => $this->customerIds['Établissements Koffi Frères']],
            ['type' => 'transfer_pending', 'severity' => 'info', 'title' => 'Transfert à valider — Plateau', 'message' => 'Le transfert TR-2026-000002 est expédié et en attente de réception', 'notifiable_type' => Transfer::class],
            ['type' => 'cash_register_open', 'severity' => 'info', 'title' => 'Caisse non clôturée', 'message' => 'La caisse de Yopougon est encore ouverte', 'notifiable_type' => CashRegister::class],
            ['type' => 'near_expiry', 'severity' => 'warning', 'title' => 'Produits proches de l\'expiration', 'message' => 'Lot de boisson JUS-001 expire dans 3 mois — planifier une promotion', 'notifiable_type' => Lot::class],
        ];

        foreach ($alerts as $a) {
            Alert::create([
                'company_id' => $this->companyId,
                'user_id' => $adminId,
                'type' => $a['type'],
                'severity' => $a['severity'],
                'title' => $a['title'],
                'message' => $a['message'],
                'notifiable_type' => $a['notifiable_type'],
                'notifiable_id' => $a['notifiable_id'] ?? null,
                'data' => null,
            ]);
        }
    }

    // ─── DOCUMENT TEMPLATES ───────────────────
    private function seedDocumentTemplates(): void
    {
        if (DocumentTemplate::where('company_id', $this->companyId)->exists()) return;

        DocumentTemplate::create([
            'company_id' => $this->companyId,
            'name' => 'Template Facture Standard',
            'type' => 'invoice',
            'colors' => ['primary' => '#6366f1', 'secondary' => '#4f46e5', 'accent' => '#e0e7ff'],
            'legal_mentions' => 'SARL au capital de 10 000 000 FCFA — RCCM: CI-ABJ-2024-01234 — CC: 12345678N',
            'terms' => 'Paiement à réception sous 30 jours — Pénalités de retard : 1.5% par mois — Toute réclamation doit être faite dans les 8 jours',
            'paper_format' => 'A4',
            'is_default' => true,
        ]);

        DocumentTemplate::create([
            'company_id' => $this->companyId,
            'name' => 'Template Bon de Livraison',
            'type' => 'delivery_note',
            'colors' => ['primary' => '#059669', 'secondary' => '#047857', 'accent' => '#d1fae5'],
            'legal_mentions' => 'SARL au capital de 10 000 000 FCFA — RCCM: CI-ABJ-2024-01234',
            'terms' => 'Le client reconnaît avoir reçu les marchandises en bon état',
            'paper_format' => 'A4',
            'is_default' => true,
        ]);

        DocumentTemplate::create([
            'company_id' => $this->companyId,
            'name' => 'Template Devis',
            'type' => 'quotation',
            'colors' => ['primary' => '#2563eb', 'secondary' => '#1d4ed8', 'accent' => '#dbeafe'],
            'legal_mentions' => 'Devis valable 30 jours — SARL au capital de 10 000 000 FCFA',
            'terms' => 'Ce devis est valable pour une durée de 30 jours à compter de sa date d\'émission',
            'paper_format' => 'A4',
            'is_default' => true,
        ]);
    }

    // ─── SUPPLIER EVALUATIONS ─────────────────
    private function seedSupplierEvaluations(): void
    {
        if (SupplierEvaluation::whereIn('supplier_id', $this->supplierIds)->exists()) return;

        $adminId = $this->userIds['admin@geststock.com'];

        $evaluations = [
            ['supplier_name' => 'AfriTech Distribution SARL', 'delays' => 4, 'quality' => 5, 'returns' => 4, 'price' => 3, 'reliability' => 4, 'volume' => 5],
            ['supplier_name' => 'Global Import CI', 'delays' => 3, 'quality' => 4, 'returns' => 3, 'price' => 4, 'reliability' => 3, 'volume' => 5],
            ['supplier_name' => 'Distri Alim SA', 'delays' => 5, 'quality' => 4, 'returns' => 4, 'price' => 4, 'reliability' => 5, 'volume' => 4],
            ['supplier_name' => 'Nouvelle Mode Africaine', 'delays' => 4, 'quality' => 5, 'returns' => 5, 'price' => 3, 'reliability' => 4, 'volume' => 3],
            ['supplier_name' => 'Pro Bureau Services', 'delays' => 5, 'quality' => 4, 'returns' => 5, 'price' => 3, 'reliability' => 4, 'volume' => 3],
        ];

        foreach ($evaluations as $ev) {
            $supId = $this->supplierIds[$ev['supplier_name']] ?? null;
            if (!$supId) continue;

            SupplierEvaluation::create([
                'supplier_id' => $supId,
                'evaluated_by' => $adminId,
                'respect_delays' => $ev['delays'],
                'product_quality' => $ev['quality'],
                'return_rate' => $ev['returns'],
                'average_price' => $ev['price'],
                'reliability' => $ev['reliability'],
                'purchase_volume' => $ev['volume'],
                'overall_rating' => (int) round(($ev['delays'] + $ev['quality'] + $ev['returns'] + $ev['price'] + $ev['reliability'] + $ev['volume']) / 6),
                'comment' => "Fournisseur " . ($ev['reliability'] >= 4 ? "fiable et réactif" : "correct mais peut mieux faire sur les délais"),
                'evaluated_at' => now()->subDays(rand(5, 30)),
            ]);
        }
    }

    // ─── SERIAL NUMBERS ───────────────────────
    private function seedSerialNumbers(): void
    {
        if (SerialNumber::where('company_id', $this->companyId)->exists()) return;

        $tv = Product::where('reference', 'TV-001')->first();
        if (!$tv) return;

        $toureId = $this->customerIds['Touré Fatim'] ?? null;

        $serials = [
            ['sn' => 'HISN-43-A1B2C3D4', 'customer_id' => null, 'status' => 'in_stock'],
            ['sn' => 'HISN-43-E5F6G7H8', 'customer_id' => $toureId, 'status' => 'sold', 'sold_at' => now()->subDay()],
            ['sn' => 'HISN-43-I9J0K1L2', 'customer_id' => null, 'status' => 'in_stock'],
        ];

        foreach ($serials as $s) {
            SerialNumber::create([
                'company_id' => $this->companyId,
                'product_id' => $tv->id,
                'customer_id' => $s['customer_id'],
                'serial_number' => $s['sn'],
                'status' => $s['status'],
                'entry_date' => now()->subMonth(),
                'sold_at' => $s['sold_at'] ?? null,
                'warranty_expiry' => isset($s['sold_at']) ? $s['sold_at']->addYear() : null,
            ]);
        }
    }
}
