<?php

namespace App\Http\Controllers;

use App\Models\ConsultationRequest;
use App\Models\Setting;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Exception;

class ConsultationController extends Controller
{
    /**
     * Display the Meet Mr. Kitonga landing and request page.
     */
    public function index()
    {
        $contactPhone = Setting::get('contact_phone', '+255 758 774 695');
        $contactEmail = Setting::get('contact_email', 'kitongafarmvillas@gmail.com');
        $consultationFee = (float) Setting::get('consultation_fee', 100000.00);

        return Inertia::render('Public/MeetMrKitonga', [
            'settings' => [
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
                'consultation_fee' => $consultationFee,
            ],
            'defaultDate' => Carbon::now()->addDay()->format('Y-m-d'),
        ]);
    }

    /**
     * Process consultation booking request and return WhatsApp payload.
     */
    public function store(Request $request)
    {
        // Rate limit: 10 consultation requests per 15 minutes per IP
        $ip = $request->ip();
        $key = 'consultation_request:' . $ip;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests submitted. Please wait a few minutes or chat directly with our concierge.',
            ], 429);
        }
        RateLimiter::hit($key, 900);

        $validated = $request->validate([
            'customer_name' => 'required|string|min:2|max:150',
            'customer_phone' => 'required|string|min:7|max:30',
            'customer_email' => 'nullable|email|max:150',
            'format' => 'required|string|in:physical,online',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|string|max:100',
            'topic' => 'required|string|max:200',
            'message' => 'nullable|string|max:1000',
        ]);

        try {
            $consultationFee = (float) Setting::get('consultation_fee', 100000.00);

            $consultation = ConsultationRequest::create([
                'reference' => ConsultationRequest::generateReference(),
                'customer_name' => trim($validated['customer_name']),
                'customer_phone' => trim($validated['customer_phone']),
                'customer_email' => !empty($validated['customer_email']) ? trim($validated['customer_email']) : null,
                'format' => $validated['format'],
                'preferred_date' => $validated['preferred_date'],
                'preferred_time' => $validated['preferred_time'],
                'fee' => $consultationFee,
                'topic' => $validated['topic'],
                'message' => !empty($validated['message']) ? trim($validated['message']) : null,
                'status' => 'request_created',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);

            $whatsappUrl = $consultation->getWhatsAppUrl();
            $whatsappMessage = $consultation->generateWhatsAppMessage();

            return response()->json([
                'success' => true,
                'message' => 'Your consultation request has been created successfully.',
                'consultation' => [
                    'id' => $consultation->id,
                    'reference' => $consultation->reference,
                    'customer_name' => $consultation->customer_name,
                    'customer_phone' => $consultation->customer_phone,
                    'customer_email' => $consultation->customer_email,
                    'format' => $consultation->format,
                    'preferred_date' => Carbon::parse($consultation->preferred_date)->format('l, d M Y'),
                    'preferred_time' => $consultation->preferred_time,
                    'fee' => (float) $consultation->fee,
                    'topic' => $consultation->topic,
                    'status' => $consultation->status,
                    'status_label' => $consultation->status_label,
                ],
                'whatsapp_url' => $whatsappUrl,
                'whatsapp_message' => $whatsappMessage,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to process consultation request. Please try again or reach out on WhatsApp.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Track when a customer clicks the WhatsApp button.
     */
    public function trackWhatsAppClick(Request $request, string $reference)
    {
        $consultation = ConsultationRequest::where('reference', $reference)->first();

        if ($consultation) {
            $consultation->update([
                'status' => ($consultation->status === 'request_created') ? 'whatsapp_initiated' : $consultation->status,
                'whatsapp_clicked_at' => Carbon::now(),
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Consultation not found.'], 404);
    }
}
