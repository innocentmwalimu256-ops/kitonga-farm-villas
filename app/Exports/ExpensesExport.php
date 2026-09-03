<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpensesExport implements FromCollection, WithHeadings
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
        $query = Expense::with('category', 'creator');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('date', [$this->startDate, $this->endDate]);
        }

        return $query->get()
            ->map(function ($e) {
                return [
                    'ID' => $e->id,
                    'Date' => $e->date,
                    'Category' => $e->category ? $e->category->name : 'Unassigned',
                    'Description' => $e->description,
                    'Amount' => $e->amount,
                    'Recipient' => $e->recipient,
                    'Status' => $e->status,
                    'Recorded By' => $e->creator ? $e->creator->name : 'System',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Expense Date',
            'Category',
            'Description',
            'Amount (TZS)',
            'Recipient',
            'Approval Status',
            'Recorded By',
        ];
    }
}
