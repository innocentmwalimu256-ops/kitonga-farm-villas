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
        $query = Booking::with('customer', 'unit.type');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('check_in', [$this->startDate, $this->endDate]);
        }

        return $query->orderBy('check_in', 'desc')->get()
            ->map(function ($b) {
                return [
                    'ID' => $b->id,
                    'Reference' => $b->reference,
                    'Customer' => $b->customer ? $b->customer->name : 'N/A',
                    'Phone' => $b->customer ? $b->customer->phone : 'N/A',
                    'ID Type' => $b->id_type ?? $b->customer?->id_type ?? 'N/A',
                    'ID Number' => $b->id_number ?? $b->customer?->id_number ?? 'N/A',
                    'Room / Unit' => $b->unit ? $b->unit->name : 'Farm Tour / Day Visit',
                    'Villa Type' => $b->unit && $b->unit->type ? $b->unit->type->name : 'N/A',
                    'Guests' => $b->guests_count ?? 1,
                    'Check In' => $b->getRawOriginal('check_in') ?? $b->check_in,
                    'Check Out' => $b->getRawOriginal('check_out') ?? $b->check_out,
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
            'Phone Number',
            'ID Type',
            'ID / Passport Number',
            'Room / Unit',
            'Villa Model',
            'Guests Count',
            'Check-In Date',
            'Check-Out Date',
            'Total Price (TZS)',
            'Amount Paid (TZS)',
            'Balance Due (TZS)',
            'Booking Status',
        ];
    }
}
