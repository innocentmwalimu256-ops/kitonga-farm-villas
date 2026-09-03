<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BookingsExport implements FromCollection, WithHeadings
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
        $query = Booking::with('customer', 'unit');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('check_in', [$this->startDate, $this->endDate]);
        }

        return $query->get()
            ->map(function ($b) {
                return [
                    'ID' => $b->id,
                    'Reference' => $b->reference,
                    'Customer' => $b->customer ? $b->customer->name : 'N/A',
                    'Room / Unit' => $b->unit ? $b->unit->name : 'N/A',
                    'Check In' => $b->check_in,
                    'Check Out' => $b->check_out,
                    'Total Price' => $b->total,
                    'Amount Paid' => $b->amount_paid,
                    'Balance' => $b->balance,
                    'Status' => $b->status,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Reference',
            'Customer Name',
            'Room / Unit',
            'Check-In Date',
            'Check-Out Date',
            'Total Price (TZS)',
            'Amount Paid (TZS)',
            'Balance Due (TZS)',
            'Booking Status',
        ];
    }
}
