<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ConsultationRequest extends Model
{
    use HasFactory;

    protected $table = 'consultation_requests';

    protected $fillable = [
        'reference',
        'customer_name',
        'customer_phone',
        'customer_email',
        'format',
        'preferred_date',
        'preferred_time',
        'fee',
        'topic',
        'message',
        'status',
        'whatsapp_clicked_at',
        'staff_notes',
        'payment_reference',
        'confirmed_date_time',
        'assigned_staff_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'preferred_date' => 'date:Y-m-d',
        'confirmed_date_time' => 'datetime',
        'whatsapp_clicked_at' => 'datetime',
        'fee' => 'decimal:2',
    ];

    /**
     * Relationship to assigned staff member.
     */
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /**
     * Generate unique reference: MK-YYYYMMDD-XXXX
     */
    public static function generateReference(): string
    {
        do {
            $datePart = Carbon::now()->format('Ymd');
            $randomPart = strtoupper(Str::random(4));
            $ref = "MK-{$datePart}-{$randomPart}";
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }

    /**
     * Human readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'request_created' => 'Request Created',
            'whatsapp_initiated' => 'WhatsApp Initiated',
            'awaiting_staff_response' => 'Awaiting Staff Response',
            'awaiting_payment' => 'Awaiting Payment Instructions',
            'payment_verified' => 'Payment Verified',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Status badge styling classes.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'request_created' => 'bg-amber-100 text-amber-800 border-amber-300',
            'whatsapp_initiated' => 'bg-blue-100 text-blue-800 border-blue-300',
            'awaiting_staff_response' => 'bg-purple-100 text-purple-800 border-purple-300',
            'awaiting_payment' => 'bg-orange-100 text-orange-800 border-orange-300',
            'payment_verified' => 'bg-teal-100 text-teal-800 border-teal-300',
            'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'completed' => 'bg-gray-100 text-gray-800 border-gray-300',
            'cancelled' => 'bg-red-100 text-red-800 border-red-300',
            default => 'bg-gray-100 text-gray-800 border-gray-300',
        };
    }

    /**
     * Build standard WhatsApp message according to the approved template.
     */
    public function generateWhatsAppMessage(): string
    {
        $formatLabel = $this->format === 'online' ? 'Online Video Call' : 'Physical at Kitonga Farm';
        $formattedDate = $this->preferred_date ? Carbon::parse($this->preferred_date)->format('l, d M Y') : 'To be arranged';
        $feeFormatted = 'TZS ' . number_format($this->fee, 0);

        return "Hello Kitonga Farm & Villas Team,\n\n" .
            "I would like to book a consultation with Mr. Kitonga.\n\n" .
            "*Consultation Details*\n" .
            "• Booking Reference: *{$this->reference}*\n" .
            "• Full Name: *{$this->customer_name}*\n" .
            "• Phone: *{$this->customer_phone}*\n" .
            "• Consultation Format: *{$formatLabel}*\n" .
            "• Preferred Date: *{$formattedDate}*\n" .
            "• Preferred Time: *{$this->preferred_time}*\n" .
            "• Consultation Fee: *{$feeFormatted}*\n" .
            "• Reason for Consultation: *{$this->topic}*\n" .
            ($this->message ? "• Additional Notes: {$this->message}\n" : "") .
            "\nPlease guide me on appointment availability, the approved payment method, payment instructions and the next steps for confirming my consultation.\n\n" .
            "Thank you.";
    }

    /**
     * Generate full official wa.me link.
     */
    public function getWhatsAppUrl(?string $phone = null): string
    {
        $targetPhone = $phone ?: Setting::get('contact_phone', '255758774695');
        $cleanPhone = preg_replace('/[^0-9]/', '', $targetPhone);
        
        // Ensure valid Tanzanian country code without leading 0
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '255' . substr($cleanPhone, 1);
        } elseif (!str_starts_with($cleanPhone, '255') && strlen($cleanPhone) === 9) {
            $cleanPhone = '255' . $cleanPhone;
        }

        $message = $this->generateWhatsAppMessage();
        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);
    }
}
