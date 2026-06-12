<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService
{
    public array $supportedEntities = [
        'products' => [
            'label' => 'Produits',
            'fields' => ['name', 'reference', 'barcode', 'category_name', 'supplier_name', 'purchase_price', 'sale_price', 'stock_quantity', 'min_stock', 'unit_sale', 'is_stockable'],
            'required' => ['name'],
            'unique' => ['reference', 'barcode'],
        ],
        'categories' => [
            'label' => 'Catégories',
            'fields' => ['name', 'slug', 'description', 'parent_name', 'margin_rate', 'is_active'],
            'required' => ['name'],
            'unique' => ['name'],
        ],
        'customers' => [
            'label' => 'Clients',
            'fields' => ['name', 'type', 'phone', 'email', 'address', 'credit_limit', 'payment_terms', 'is_active'],
            'required' => ['name'],
            'unique' => ['email', 'phone'],
        ],
        'suppliers' => [
            'label' => 'Fournisseurs',
            'fields' => ['name', 'type', 'contact_name', 'phone', 'email', 'address', 'payment_terms', 'delivery_delay_days', 'is_active'],
            'required' => ['name'],
            'unique' => ['email'],
        ],
        'stock' => [
            'label' => 'Stocks initiaux',
            'fields' => ['product_reference', 'product_barcode', 'quantity', 'unit_cost', 'lot_number', 'expiry_date'],
            'required' => ['quantity'],
            'unique' => [],
            'match' => ['product_reference', 'product_barcode'],
        ],
        'prices' => [
            'label' => 'Prix',
            'fields' => ['product_reference', 'product_barcode', 'sale_price', 'wholesale_price', 'promo_price', 'purchase_price'],
            'required' => ['sale_price'],
            'unique' => [],
            'match' => ['product_reference', 'product_barcode'],
        ],
        'users' => [
            'label' => 'Utilisateurs',
            'fields' => ['name', 'email', 'password', 'first_name', 'last_name', 'phone', 'role', 'is_active'],
            'required' => ['name', 'email'],
            'unique' => ['email'],
        ],
    ];

    public function parseFile(string $filePath, string $entity): array
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($extension === 'csv') {
            return $this->parseCsv($filePath);
        }

        if (in_array($extension, ['xls', 'xlsx'])) {
            return $this->parseExcel($filePath);
        }

        throw new \InvalidArgumentException('Format non supporté. Utilisez CSV ou Excel.');
    }

    protected function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Impossible de lire le fichier.');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return [];
        }

        $headers = array_map('trim', $headers);

        while (($line = fgetcsv($handle)) !== false) {
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = isset($line[$i]) ? trim($line[$i]) : '';
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    protected function parseExcel(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        if (empty($data)) return [];

        $headers = array_map('trim', array_shift($data));
        $rows = [];

        foreach ($data as $line) {
            $row = [];
            foreach ($headers as $i => $header) {
                $row[$header] = isset($line[$i]) ? trim((string) $line[$i]) : '';
            }
            $rows[] = $row;
        }

        $spreadsheet->disconnectWorksheets();
        return $rows;
    }

    public function validate(array $rows, string $entity, int $companyId): array
    {
        $config = $this->supportedEntities[$entity] ?? null;
        if (!$config) {
            throw new \InvalidArgumentException("Entité inconnue : {$entity}");
        }

        $results = [];
        $errors = [];
        $warnings = [];
        $duplicates = [];
        $matchedIds = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $rowErrors = [];
            $rowWarnings = [];
            $isDuplicate = false;

            foreach ($config['required'] as $field) {
                if (empty($row[$field]) && $row[$field] !== '0') {
                    $rowErrors[] = "Le champ '{$field}' est obligatoire (ligne {$rowNum}).";
                }
            }

            if (in_array($entity, ['stock', 'prices'])) {
                $ref = $row['product_reference'] ?? '';
                $barcode = $row['product_barcode'] ?? '';
                if (empty($ref) && empty($barcode)) {
                    $rowErrors[] = "Fournissez au moins la référence ou le code-barres (ligne {$rowNum}).";
                } else {
                    $product = null;
                    if ($ref) {
                        $product = Product::where('company_id', $companyId)
                            ->where('reference', $ref)->first();
                    }
                    if (!$product && $barcode) {
                        $product = Product::where('company_id', $companyId)
                            ->where('barcode', $barcode)->first();
                    }
                    if ($product) {
                        $matchedIds[$index] = $product->id;
                    } else {
                        $rowErrors[] = "Produit introuvable (réf: {$ref}, code: {$barcode}) ligne {$rowNum}.";
                    }
                }
            }

            foreach ($config['unique'] as $field) {
                $value = $row[$field] ?? '';
                if (empty($value)) continue;

                $exists = match ($entity) {
                    'products' => Product::where('company_id', $companyId)
                        ->where($field, $value)->exists(),
                    'categories' => Category::where('company_id', $companyId)
                        ->where($field, $value)->exists(),
                    'customers' => Customer::where('company_id', $companyId)
                        ->where($field, $value)->exists(),
                    'suppliers' => Supplier::where('company_id', $companyId)
                        ->where($field, $value)->exists(),
                    'users' => User::where('company_id', $companyId)
                        ->where($field, $value)->exists(),
                    default => false,
                };

                if ($exists) {
                    $rowWarnings[] = "Doublon potentiel : '{$field}' = '{$value}' existe déjà.";
                    $isDuplicate = true;
                }
            }

            $results[] = [
                'row' => $rowNum,
                'data' => $row,
                'errors' => $rowErrors,
                'warnings' => $rowWarnings,
                'is_duplicate' => $isDuplicate,
                'is_valid' => empty($rowErrors),
                'matched_product_id' => $matchedIds[$index] ?? null,
            ];
        }

        return [
            'total' => count($rows),
            'valid' => count(array_filter($results, fn($r) => $r['is_valid'])),
            'invalid' => count(array_filter($results, fn($r) => !$r['is_valid'])),
            'duplicates' => count(array_filter($results, fn($r) => $r['is_duplicate'])),
            'results' => $results,
        ];
    }

    public function import(array $validationResult, string $entity, int $companyId, ?int $userId = null): array
    {
        $config = $this->supportedEntities[$entity] ?? null;
        if (!$config) throw new \InvalidArgumentException("Entité inconnue : {$entity}");

        $imported = 0;
        $failed = 0;
        $log = [];

        DB::beginTransaction();
        try {
            foreach ($validationResult['results'] as $item) {
                if (!$item['is_valid']) {
                    $failed++;
                    $log[] = "Ligne {$item['row']}: ignorée (erreurs)";
                    continue;
                }

                $data = $item['data'];
                $success = false;

                try {
                    $success = match ($entity) {
                        'products' => $this->importProduct($data, $companyId),
                        'categories' => $this->importCategory($data, $companyId),
                        'customers' => $this->importCustomer($data, $companyId),
                        'suppliers' => $this->importSupplier($data, $companyId),
                        'stock' => $this->importStock($data, $companyId, $item['matched_product_id']),
                        'prices' => $this->importPrices($data, $companyId, $item['matched_product_id']),
                        'users' => $this->importUser($data, $companyId),
                        default => false,
                    };
                } catch (\Exception $e) {
                    $log[] = "Ligne {$item['row']}: erreur - {$e->getMessage()}";
                    $failed++;
                    continue;
                }

                if ($success) {
                    $imported++;
                    $log[] = "Ligne {$item['row']}: importée";
                } else {
                    $failed++;
                    $log[] = "Ligne {$item['row']}: échouée";
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'total' => $validationResult['total'],
            'imported' => $imported,
            'failed' => $failed,
            'log' => $log,
        ];
    }

    protected function importProduct(array $data, int $companyId): bool
    {
        $categoryId = null;
        if (!empty($data['category_name'])) {
            $category = Category::firstOrCreate(
                ['company_id' => $companyId, 'name' => $data['category_name']],
                ['slug' => str($data['category_name'])->slug()],
            );
            $categoryId = $category->id;
        }

        $supplierId = null;
        if (!empty($data['supplier_name'])) {
            $supplier = Supplier::firstOrCreate(
                ['company_id' => $companyId, 'name' => $data['supplier_name']],
            );
            $supplierId = $supplier->id;
        }

        Product::create([
            'company_id' => $companyId,
            'category_id' => $categoryId,
            'supplier_id' => $supplierId,
            'name' => $data['name'] ?? '',
            'reference' => $data['reference'] ?? null,
            'barcode' => $data['barcode'] ?? null,
            'purchase_price' => floatval($data['purchase_price'] ?? 0),
            'sale_price' => floatval($data['sale_price'] ?? 0),
            'stock_quantity' => floatval($data['stock_quantity'] ?? 0),
            'min_stock' => floatval($data['min_stock'] ?? 0),
            'unit_sale' => $data['unit_sale'] ?? 'piece',
            'is_stockable' => filter_var($data['is_stockable'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        return true;
    }

    protected function importCategory(array $data, int $companyId): bool
    {
        $parentId = null;
        if (!empty($data['parent_name'])) {
            $parent = Category::where('company_id', $companyId)
                ->where('name', $data['parent_name'])->first();
            $parentId = $parent?->id;
        }

        $slug = !empty($data['slug']) ? $data['slug'] : str($data['name'])->slug();

        if (Category::where('company_id', $companyId)->where('name', $data['name'])->exists()) {
            return false;
        }

        Category::create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'parent_id' => $parentId,
            'margin_rate' => floatval($data['margin_rate'] ?? 0),
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        return true;
    }

    protected function importCustomer(array $data, int $companyId): bool
    {
        Customer::create([
            'company_id' => $companyId,
            'name' => $data['name'] ?? '',
            'type' => $data['type'] ?? 'particular',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'credit_limit' => floatval($data['credit_limit'] ?? 0),
            'payment_terms' => $data['payment_terms'] ?? null,
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        return true;
    }

    protected function importSupplier(array $data, int $companyId): bool
    {
        Supplier::create([
            'company_id' => $companyId,
            'name' => $data['name'] ?? '',
            'type' => $data['type'] ?? 'supplier',
            'contact_name' => $data['contact_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'delivery_delay_days' => intval($data['delivery_delay_days'] ?? 0),
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        return true;
    }

    protected function importStock(array $data, int $companyId, ?int $productId): bool
    {
        if (!$productId) return false;

        $product = Product::find($productId);
        if (!$product) return false;

        $quantity = floatval($data['quantity'] ?? 0);
        $product->increment('stock_quantity', $quantity);

        if (!empty($data['unit_cost'])) {
            $product->update(['purchase_price' => floatval($data['unit_cost'])]);
        }

        if (!empty($data['lot_number']) || !empty($data['expiry_date'])) {
            $product->lots()->create([
                'company_id' => $companyId,
                'lot_number' => $data['lot_number'] ?? 'LOT-' . now()->format('Ymd'),
                'expiry_date' => !empty($data['expiry_date']) ? $data['expiry_date'] : null,
                'initial_quantity' => $quantity,
                'remaining_quantity' => $quantity,
            ]);
        }

        return true;
    }

    protected function importPrices(array $data, int $companyId, ?int $productId): bool
    {
        if (!$productId) return false;

        $update = [];
        if (isset($data['sale_price']) && $data['sale_price'] !== '') {
            $update['sale_price'] = floatval($data['sale_price']);
        }
        if (isset($data['wholesale_price']) && $data['wholesale_price'] !== '') {
            $update['wholesale_price'] = floatval($data['wholesale_price']);
        }
        if (isset($data['promo_price']) && $data['promo_price'] !== '') {
            $update['promo_price'] = floatval($data['promo_price']);
        }
        if (isset($data['purchase_price']) && $data['purchase_price'] !== '') {
            $update['purchase_price'] = floatval($data['purchase_price']);
        }

        if (!empty($update)) {
            Product::where('id', $productId)->update($update);
        }

        return true;
    }

    protected function importUser(array $data, int $companyId): bool
    {
        if (User::where('email', $data['email'])->exists()) {
            return false;
        }

        User::create([
            'company_id' => $companyId,
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'password' => Hash::make($data['password'] ?? 'password'),
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);

        if (!empty($data['role'])) {
            $user = User::where('email', $data['email'])->first();
            $user?->assignRole($data['role']);
        }

        return true;
    }

    public function getTemplateHeaders(string $entity): array
    {
        $config = $this->supportedEntities[$entity] ?? null;
        if (!$config) throw new \InvalidArgumentException("Entité inconnue : {$entity}");
        return $config['fields'];
    }

    public function generateTemplateCsv(string $entity): string
    {
        $headers = $this->getTemplateHeaders($entity);
        $output = fopen('php://temp', 'w+');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers);

        $sample = $this->getSampleRow($entity);
        if ($sample) {
            fputcsv($output, $sample);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    protected function getSampleRow(string $entity): ?array
    {
        return match ($entity) {
            'products' => ['Produit exemple', 'REF-001', '123456789', 'Catégorie exemple', '', '1000', '2500', '50', '5', 'piece', '1'],
            'categories' => ['Catégorie exemple', 'categorie-exemple', 'Description', '', '15', '1'],
            'customers' => ['Client exemple', 'particular', '771234567', 'client@exemple.com', 'Adresse', '100000', '30 jours', '1'],
            'suppliers' => ['Fournisseur exemple', 'supplier', 'Contact', '771234567', 'fournisseur@exemple.com', 'Adresse', '45 jours', '30', '1'],
            'stock' => ['REF-001', '123456789', '100', '1500', 'LOT-001', '2026-12-31'],
            'prices' => ['REF-001', '123456789', '2500', '2000', '2200', '1000'],
            'users' => ['User', 'user@exemple.com', 'password123', 'Prénom', 'Nom', '771234567', 'admin', '1'],
            default => null,
        };
    }
}
