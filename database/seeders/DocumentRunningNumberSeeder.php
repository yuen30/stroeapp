<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\DocumentRunningNumber;
use Illuminate\Database\Seeder;

class DocumentRunningNumberSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Global Sequences (for Company itself)
        $this->createDefaults(null, null, [
            'company' => ['prefix' => 'COMP', 'date_format' => '', 'field' => 'code', 'length' => 3],
            'unit' => ['prefix' => 'UNIT', 'date_format' => '', 'field' => 'code', 'length' => 6],
            'brand' => ['prefix' => 'BRAND', 'date_format' => '', 'field' => 'code', 'length' => 6],
            'category' => ['prefix' => 'CAT', 'date_format' => '', 'field' => 'code', 'length' => 6],
        ]);

        $companies = Company::all();

        foreach ($companies as $company) {
            // 2. Company level sequences
            $this->createDefaults($company->id, null, [
                'branch' => ['prefix' => 'BR', 'date_format' => '', 'field' => 'code', 'length' => 3],
                'customer' => ['prefix' => 'CUS', 'date_format' => '', 'field' => 'code', 'length' => 6],
                'supplier' => ['prefix' => 'SUP', 'date_format' => '', 'field' => 'code', 'length' => 6],
                'contact' => ['prefix' => 'CONT', 'date_format' => '', 'field' => 'code', 'length' => 6],
            ]);

            $branches = $company->branches;

            // 3. Branch level sequences
            foreach ($branches as $branch) {
                $this->createDefaults($company->id, $branch->id, [
                    'sale_order' => ['prefix' => 'TGP-CA', 'date_format' => 'Ym', 'field' => 'invoice_number', 'length' => 5],
                    'purchase_order' => ['prefix' => 'PO', 'date_format' => 'Ym', 'field' => 'order_number', 'length' => 5],
                    'tax_invoice' => ['prefix' => 'INV', 'date_format' => 'Ym', 'field' => 'tax_invoice_number', 'length' => 5],
                    'goods_receipt' => ['prefix' => 'GR', 'date_format' => 'Ym', 'field' => 'receipt_number', 'length' => 5],
                    'product' => ['prefix' => 'PROD', 'date_format' => '', 'field' => 'code', 'length' => 6],
                    'stock_reservation' => ['prefix' => 'RESV', 'date_format' => 'Ym', 'field' => 'code', 'length' => 5],
                ]);
            }
        }
    }

    private function createDefaults($companyId, $branchId, array $types): void
    {
        foreach ($types as $type => $config) {
            DocumentRunningNumber::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'document_type' => $type,
                ],
                [
                    'prefix' => $config['prefix'],
                    'date_format' => $config['date_format'],
                    'running_length' => $config['length'] ?? 5,
                    'current_number' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
