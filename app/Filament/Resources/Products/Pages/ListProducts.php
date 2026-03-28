<?php

namespace App\Filament\Resources\Products\Pages;

use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('เพิ่มสินค้า')
                ->icon('heroicon-o-plus-circle'),
            Action::make('download_template')
                ->label('ดาวน์โหลดรูปแบบ')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    return Excel::download(
                        new ProductsTemplateExport(),
                        'products-template.xlsx'
                    );
                }),
            Action::make('import')
                ->label('นำเข้า Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->schema([
                    FileUpload::make('file')
                        ->label('ไฟล์ Excel')
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv',
                        ])
                        ->required()
                        ->helperText('รองรับไฟล์ .xlsx, .xls, .csv (คอลัมน์: ชื่อ, รหัส, บาร์โค้ด, รายละเอียด, ราคาทุน, ราคาขาย, จำนวนสต็อก, หมวดหมู่, ยี่ห้อ, หน่วยนับ)')
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private')
                        ->storeFileNamesIn('original_filename'),
                ])
                ->action(function (array $data) {
                    // Filament FileUpload คืนค่าเป็น array หรือ string
                    $fileData = $data['file'];
                    $relativePath = is_array($fileData) ? $fileData[0] : $fileData;

                    // Filament บันทึกไฟล์ private ที่ storage/app/private/
                    $filePath = storage_path('app/private/' . $relativePath);

                    // ตรวจสอบว่าไฟล์มีอยู่จริง
                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->danger()
                            ->title('ไม่พบไฟล์')
                            ->body('ไม่สามารถอ่านไฟล์ที่อัปโหลดได้: ' . $filePath)
                            ->send();
                        return;
                    }

                    try {
                        $rows = Excel::toArray([], $filePath)[0];
                        array_shift($rows);  // Remove header row

                        $imported = 0;
                        $errors = [];

                        foreach ($rows as $index => $row) {
                            try {
                                // หาหรือสร้าง category_id จากชื่อ
                                $categoryId = null;
                                if (!empty($row[7])) {
                                    $category = Category::firstOrCreate(
                                        ['name' => $row[7]],
                                        ['is_active' => true]
                                    );
                                    $categoryId = $category->id;
                                }

                                // หาหรือสร้าง brand_id จากชื่อ (บังคับ)
                                $brandId = null;
                                if (!empty($row[8])) {
                                    $brand = Brand::firstOrCreate(
                                        ['name' => $row[8]],
                                        ['is_active' => true]
                                    );
                                    $brandId = $brand->id;
                                }

                                // หาหรือสร้าง unit_id จากชื่อ (บังคับ)
                                $unitId = null;
                                if (!empty($row[9])) {
                                    $unit = Unit::firstOrCreate(
                                        ['name' => $row[9]],
                                        ['is_active' => true]
                                    );
                                    $unitId = $unit->id;
                                }

                                $productData = [
                                    'name' => $row[0] ?? null,
                                    'code' => !empty($row[1]) ? $row[1] : null,
                                    'barcode' => $row[2] ?? null,
                                    'description' => $row[3] ?? null,
                                    'cost_price' => $row[4] ?? 0,
                                    'selling_price' => $row[5] ?? 0,
                                    'stock_quantity' => $row[6] ?? 0,
                                    'category_id' => $categoryId,
                                    'brand_id' => $brandId,
                                    'unit_id' => $unitId,
                                    'company_id' => Auth::user()->company_id,
                                    'branch_id' => Auth::user()->branch_id,
                                    'is_active' => true,
                                ];

                                if (!empty($productData['name']) && !empty($brandId) && !empty($unitId)) {
                                    Product::create($productData);
                                    $imported++;
                                } elseif (!empty($productData['name'])) {
                                    throw new \Exception('ยี่ห้อและหน่วยนับเป็นฟิลด์บังคับ');
                                }
                            } catch (\Exception $e) {
                                $errors[] = 'แถว ' . ($index + 2) . ': ' . $e->getMessage();
                            }
                        }

                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }

                        if ($imported > 0) {
                            Notification::make()
                                ->success()
                                ->title('นำเข้าสำเร็จ')
                                ->body("นำเข้าสินค้าสำเร็จ {$imported} รายการ"
                                    . (count($errors) > 0 ? ' (มีข้อผิดพลาด ' . count($errors) . ' รายการ)' : ''))
                                ->send();
                        }

                        if (count($errors) > 0) {
                            Notification::make()
                                ->warning()
                                ->title('มีข้อผิดพลาดบางรายการ')
                                ->body(implode("\n", array_slice($errors, 0, 5)))
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('เกิดข้อผิดพลาด')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->modalHeading('นำเข้าสินค้าจาก Excel')
                ->modalDescription('อัปโหลดไฟล์ Excel ที่มีข้อมูลสินค้า')
                ->modalSubmitActionLabel('นำเข้า')
                ->modalWidth('md'),
            Action::make('export')
                ->label('ส่งออก Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    return Excel::download(
                        new ProductsExport(),
                        'products-' . date('Y-m-d-His') . '.xlsx'
                    );
                }),
        ];
    }
}
