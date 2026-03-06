<?php

namespace App\Filament\Resources\TaxInvoices\Pages;

use App\Filament\Resources\TaxInvoices\TaxInvoiceResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewTaxInvoice extends ViewRecord
{
    protected static string $resource = TaxInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('พิมพ์/PDF')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->action(function () {
                    return response()->streamDownload(function () {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.tax-invoice', [
                            'taxInvoice' => $this->record->load([
                                'company',
                                'branch',
                                'customer',
                                'saleOrder.items.product.unit',
                                'creator',
                            ]),
                        ]);
                        echo $pdf->stream();
                    }, 'TAX-INV-' . $this->record->tax_invoice_number . '.pdf');
                }),
            EditAction::make()
                ->label('แก้ไข')
                ->icon('heroicon-o-pencil-square'),
            DeleteAction::make()
                ->label('ลบ')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->modalHeading('ลบใบกำกับภาษี')
                ->modalDescription('คุณแน่ใจหรือไม่ว่าต้องการลบใบกำกับภาษีนี้?')
                ->modalSubmitActionLabel('ยืนยันการลบ'),
        ];
    }
}
