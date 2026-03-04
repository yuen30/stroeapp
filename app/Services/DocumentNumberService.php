<?php

namespace App\Services;

use App\Models\DocumentRunningNumber;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * สร้างเลขที่เอกสารอัตโนมัติ
     *
     * @param string $documentType ประเภทเอกสาร (PO, SO, INV, GR)
     * @param string|null $companyId
     * @param string|null $branchId
     * @return string
     */
    public function generate(
        string $documentType,
        ?string $companyId = null,
        ?string $branchId = null
    ): string {
        return DB::transaction(function () use ($documentType, $companyId, $branchId) {
            // ค้นหาหรือสร้าง running number config
            $config = DocumentRunningNumber::where('document_type', $documentType)
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$config) {
                // สร้าง config ใหม่ถ้ายังไม่มี
                $config = $this->createDefaultConfig($documentType, $companyId, $branchId);
            }

            // เพิ่มเลขที่
            $config->increment('current_number');
            $config->refresh();

            // สร้างเลขที่เอกสาร
            return $this->formatDocumentNumber($config);
        });
    }

    /**
     * สร้าง config เริ่มต้น
     */
    private function createDefaultConfig(
        string $documentType,
        ?string $companyId,
        ?string $branchId
    ): DocumentRunningNumber {
        $prefixes = [
            'PO' => 'PO',
            'SO' => 'SO',
            'INV' => 'INV',
            'GR' => 'GR',
        ];

        return DocumentRunningNumber::create([
            'company_id' => $companyId ?? $this->getDefaultCompanyId(),
            'branch_id' => $branchId,
            'document_type' => $documentType,
            'prefix' => $prefixes[$documentType] ?? $documentType,
            'date_format' => 'Y',  // ใช้ปี เช่น 2026
            'running_length' => 4,
            'current_number' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * จัดรูปแบบเลขที่เอกสาร
     */
    private function formatDocumentNumber(DocumentRunningNumber $config): string
    {
        $parts = [];

        // Prefix
        if ($config->prefix) {
            $parts[] = $config->prefix;
        }

        // Date component
        if ($config->date_format) {
            $parts[] = date($config->date_format);
        }

        // Running number
        $runningNumber = str_pad(
            (string) $config->current_number,
            $config->running_length,
            '0',
            STR_PAD_LEFT
        );

        return implode('', $parts) . '-' . $runningNumber;
    }

    /**
     * ดึง company_id เริ่มต้น (company แรกในระบบ)
     */
    private function getDefaultCompanyId(): string
    {
        return DB::table('companies')->orderBy('created_at')->value('id');
    }
}
