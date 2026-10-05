<?php

namespace App\Exports;

use App\Models\InventoryMovement;
use App\Models\SaleItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FarmingExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = InventoryMovement::with('product.category', 'creator');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        }

        return $query->orderBy('created_at', 'desc')->get()
            ->map(function ($m) {
                return [
                    'ID' => $m->id,
                    'Date' => $m->created_at->format('Y-m-d H:i'),
                    'Product / Item' => $m->product ? $m->product->name : 'N/A',
                    'Category' => $m->product && $m->product->category ? $m->product->category->name : 'Farm Produce',
                    'Movement Type' => ucfirst($m->type),
                    'Quantity' => $m->quantity,
                    'Unit' => $m->product ? $m->product->unit : 'unit',
                    'Reason / Harvest Note' => $m->reason ?? 'Routine harvest / stock operation',
                    'Recorded By' => $m->creator ? $m->creator->name : 'System',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date & Time',
            'Farm Produce / Item',
            'Category',
            'Operation Type',
            'Quantity',
            'Unit of Measure',
            'Harvest Note / Reason',
            'Staff Recorder',
        ];
    }
}
