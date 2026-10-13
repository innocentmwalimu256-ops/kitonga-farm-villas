<?php

namespace App\Exports;

use App\Models\ConsultationRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KitongaExport implements FromCollection, WithHeadings
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
        if (!Schema::hasTable('consultation_requests')) {
            return collect();
        }

        $query = ConsultationRequest::query();

        if ($this->startDate && $this->endDate) {
            $query->where(function ($q) {
                $q->whereBetween('preferred_date', [$this->startDate, $this->endDate])
                  ->orWhereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            });
        }

        return $query->orderBy('created_at', 'desc')->get()
            ->map(function ($c) {
                $statusLabels = [
                    'request_created' => 'Imepokelewa (Request Created)',
                    'whatsapp_initiated' => 'Mawasiliano WhatsApp',
                    'awaiting_payment' => 'Inasubiri Malipo',
                    'payment_verified' => 'Malipo Yamethibitishwa',
                    'confirmed' => 'Imethibitishwa (Confirmed)',
                    'completed' => 'Imekamilika (Completed)',
                    'cancelled' => 'Imeghairiwa (Cancelled)',
                ];

                $formatLabels = [
                    'in_person' => 'Shambani (In-Person Farm Visit)',
                    'video_call' => 'Mtandaoni (Video Call / Google Meet)',
                    'phone_call' => 'Simu ya Sauti (Phone Call)',
                ];

                return [
                    'ID' => $c->id,
                    'Reference' => $c->reference,
                    'Customer Name' => $c->customer_name,
                    'Phone Number' => $c->customer_phone,
                    'Email' => $c->customer_email ?: 'N/A',
                    'Format' => $formatLabels[$c->format] ?? ($c->format ?: 'General'),
                    'Date' => $c->preferred_date ? Carbon::parse($c->preferred_date)->format('Y-m-d') : 'N/A',
                    'Time' => $c->preferred_time ?: 'N/A',
                    'Topic / Area' => $c->topic ?: 'General Mentorship',
                    'Fee (TZS)' => (float) ($c->fee ?: 0),
                    'Payment Status' => in_array($c->status, ['confirmed', 'payment_verified', 'completed']) ? 'Paid / Verified' : 'Pending',
                    'Payment Reference' => $c->payment_reference ?: 'N/A',
                    'Status' => $statusLabels[$c->status] ?? $c->status,
                    'Staff Notes' => $c->staff_notes ?: 'N/A',
                    'Created At' => $c->created_at ? $c->created_at->format('Y-m-d H:i') : 'N/A',
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
            'Email Address',
            'Consultation Format',
            'Preferred Date',
            'Preferred Time',
            'Topic / Focus Area',
            'Fee (TZS)',
            'Payment Verification',
            'Payment Reference',
            'Current Status',
            'Staff Internal Notes',
            'Created Date',
        ];
    }
}
