<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\BookingItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ToursExport implements FromCollection, WithHeadings
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
        $query = BookingItem::where('item_type', 'tour')
            ->with(['booking.customer']);

        if ($this->startDate && $this->endDate) {
            $query->whereHas('booking', function ($q) {
                $q->whereBetween('check_in', [$this->startDate, $this->endDate]);
            });
        }

        return $query->get()
            ->map(function ($item) {
                $booking = $item->booking;
                return [
                    'ID' => $item->id,
                    'Reference' => $booking ? $booking->reference : 'N/A',
                    'Tour Experience' => $item->description_snapshot,
                    'Date' => $booking ? ($booking->getRawOriginal('check_in') ?? $booking->check_in) : 'N/A',
                    'Visitors Count' => $item->quantity,
                    'Rate per Person' => $item->unit_price_snapshot,
                    'Total Amount' => $item->total,
                    'Lead Visitor' => $booking && $booking->customer ? $booking->customer->name : 'N/A',
                    'Phone' => $booking && $booking->customer ? $booking->customer->phone : 'N/A',
                    'Booking Status' => $booking ? $booking->status : 'N/A',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Booking Reference',
            'Tour Experience Name',
            'Date Scheduled',
            'Visitors Count',
            'Price per Person (TZS)',
            'Total Revenue (TZS)',
            'Visitor Name',
            'Phone Number',
            'Status',
        ];
    }
}
