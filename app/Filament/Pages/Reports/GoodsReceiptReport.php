<?php

namespace App\Filament\Pages\Reports;

use App\Enums\OrderStatus;
use App\Models\GoodsReceipt;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GoodsReceiptReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected string $view = 'filament.pages.reports.goods-receipt-report';

    protected static string|\UnitEnum|null $navigationGroup = '3. คลังสินค้า (Inventory)';

    protected static ?int $navigationSort = 7;

    protected static ?string $title = 'รายงานการรับสินค้า';

    protected static ?string $navigationLabel = 'รายงานการรับสินค้า';

    public ?array $data = [];

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Pages\Reports\Widgets\GoodsReceiptOverview::class,
        ];
    }

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('ตั้งค่าตัวกรองรายงาน')
                    ->description('ข้อมูลการรับสินค้าจะเปลี่ยนไปตามเงื่อนไขที่คุณระบุ')
                    ->icon('heroicon-m-funnel')
                    ->extraAttributes(['class' => 'mb-6'])
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                DatePicker::make('date_from')
                                    ->label('จากวันที่')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->default(now()->startOfMonth())
                                    ->maxDate(fn($get) => $get('date_to'))
                                    ->live(),
                                DatePicker::make('date_to')
                                    ->label('ถึงวันที่')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->default(now())
                                    ->minDate(fn($get) => $get('date_from'))
                                    ->live(),
                                Select::make('status')
                                    ->label('สถานะ')
                                    ->options([
                                        'all' => 'ทั้งหมด',
                                        OrderStatus::Draft->value => 'ร่าง',
                                        OrderStatus::Confirmed->value => 'ยืนยันแล้ว',
                                        OrderStatus::PartiallyReceived->value => 'รับบางส่วน',
                                        OrderStatus::Completed->value => 'เสร็จสิ้น',
                                        OrderStatus::Cancelled->value => 'ยกเลิก',
                                    ])
                                    ->default('all')
                                    ->native(false)
                                    ->live(),
                                Select::make('supplier_id')
                                    ->label('ซัพพลายเออร์')
                                    ->options(\App\Models\Supplier::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->live(),
                            ]),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                GoodsReceipt::query()
                    ->with(['supplier', 'branch', 'purchaseOrder', 'items.product.unit', 'creator'])
                    ->when(
                        $this->data['date_from'] ?? null,
                        fn(Builder $query, $date) => $query->whereDate('document_date', '>=', $date)
                    )
                    ->when(
                        $this->data['date_to'] ?? null,
                        fn(Builder $query, $date) => $query->whereDate('document_date', '<=', $date)
                    )
                    ->when(
                        $this->data['status'] ?? 'all',
                        fn(Builder $query, $status) => $status !== 'all' ? $query->where('status', $status) : $query
                    )
                    ->when(
                        $this->data['supplier_id'] ?? null,
                        fn(Builder $query, $supplierId) => $query->where('supplier_id', $supplierId)
                    )
            )
            ->columns([
                TextColumn::make('row_id')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter(),
                TextColumn::make('document_date')
                    ->label('วันที่รับของ')
                    ->icon(Heroicon::Calendar)
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn($record) => $record->created_at->format('H:i')),
                TextColumn::make('receipt_number')
                    ->label('เอกสารอ้างอิง')
                    ->icon(Heroicon::DocumentText)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->url(fn($record) => route('filament.store.resources.goods-receipts.view', $record))
                    ->color('primary')
                    ->description(fn($record) => 'PO: ' . ($record->purchaseOrder?->order_number ?? '-')),
                TextColumn::make('supplier.name')
                    ->label('ซัพพลายเออร์')
                    ->icon(Heroicon::BuildingOffice2)
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record) => 'สาขา: ' . ($record->branch?->name ?? 'สำนักงานใหญ่')),
                TextColumn::make('items_count')
                    ->label('รายการ')
                    ->icon(Heroicon::ListBullet)
                    ->counts('items')
                    ->formatStateUsing(fn($state) => number_format($state) . ' รายการ')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('total_quantity')
                    ->label('จำนวนรับเข้า')
                    ->icon(Heroicon::ArchiveBox)
                    ->getStateUsing(fn($record) => $record->items->sum('quantity'))
                    ->formatStateUsing(fn($state) => '+'.number_format($state) . ' หน่วย')
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('status')
                    ->label('สถานะ')
                    ->badge()
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('บันทึกโดย')
                    ->icon(Heroicon::User)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('document_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('สถานะ')
                    ->options([
                        OrderStatus::Draft->value => 'ร่าง',
                        OrderStatus::Confirmed->value => 'ยืนยันแล้ว',
                        OrderStatus::Cancelled->value => 'ยกเลิก',
                    ])
                    ->native(false),
                SelectFilter::make('supplier_id')
                    ->label('ซัพพลายเออร์')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
                SelectFilter::make('branch_id')
                    ->label('สาขา')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->emptyStateHeading('ไม่พบข้อมูลการรับสินค้า')
            ->emptyStateDescription('ไม่มีข้อมูลการรับสินค้าในช่วงเวลาที่เลือก')
            ->emptyStateIcon(Heroicon::InboxStack);
    }

    public function getHeading(): string
    {
        $query = GoodsReceipt::query();

        if ($dateFrom = $this->data['date_from'] ?? null) {
            $query->whereDate('document_date', '>=', $dateFrom);
        }

        if ($dateTo = $this->data['date_to'] ?? null) {
            $query->whereDate('document_date', '<=', $dateTo);
        }

        if ($status = $this->data['status'] ?? 'all') {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($supplierId = $this->data['supplier_id'] ?? null) {
            $query->where('supplier_id', $supplierId);
        }

        $count = $query->count();
        $totalQuantity = $query->with('items')->get()->sum(fn($gr) => $gr->items->sum('quantity'));

        return 'รายงานการรับสินค้า (' . number_format($count) . ' ใบ | ' . number_format($totalQuantity) . ' หน่วย)';
    }
}
