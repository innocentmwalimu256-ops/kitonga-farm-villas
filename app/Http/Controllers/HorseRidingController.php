<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HorseRidingController extends Controller
{
    /**
     * Display the Horse Riding Experience page.
     */
    public function index()
    {
        $contactPhone = '+255 758 774 695';
        $contactEmail = 'kitongafarmvillas@gmail.com';

        try {
            $contactPhone = Setting::get('contact_phone', $contactPhone);
            $contactEmail = Setting::get('contact_email', $contactEmail);
        } catch (\Throwable $e) {
            // Graceful fallback to verified defaults if database is offline
        }

        // Clean WhatsApp number
        $cleanPhone = preg_replace('/[^0-9]/', '', $contactPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '255' . substr($cleanPhone, 1);
        } elseif (!str_starts_with($cleanPhone, '255') && strlen($cleanPhone) === 9) {
            $cleanPhone = '255' . $cleanPhone;
        }

        $defaultDate = Carbon::tomorrow()->format('Y-m-d');

        // Check if custom user video exists
        $hasCustomVideo = file_exists(public_path('videos/horse-riding-main.mp4'));

        return Inertia::render('Public/HorseRiding', [
            'settings' => [
                'contact_phone' => $contactPhone,
                'contact_email' => $contactEmail,
                'whatsapp_number' => $cleanPhone ?: '255758774695',
                'location' => 'Komkonga Village, Tanga Region, Tanzania',
            ],
            'experience' => [
                'title' => 'Horse Riding Experience at Kitonga Farm',
                'rate_per_person_per_hour' => 50000,
                'currency' => 'TZS',
                'location' => 'Kitonga Farm, Komkonga, Tanga, Tanzania',
                'opening_hours' => 'Daily 08:00 AM – 06:00 PM',
                'what_to_bring' => 'Comfortable riding trousers, closed shoes or boots, and sun protection.',
                'good_to_know' => 'Advance booking recommended. Safety orientation provided before every session.',
            ],
            'defaultDate' => $defaultDate,
            'hasCustomVideo' => $hasCustomVideo,
        ]);
    }

    /**
     * Stream the horse riding hero video with high-performance byte-range support.
     */
    public function streamVideo(Request $request)
    {
        $path = public_path('videos/horse-riding-main.mp4');

        if (!file_exists($path)) {
            // Graceful fallback to existing optimized cinematic video
            if (file_exists(public_path('videos/hero_cinematic.mp4'))) {
                $path = public_path('videos/hero_cinematic.mp4');
            } elseif (file_exists(public_path('videos/IMG_2249.mp4'))) {
                $path = public_path('videos/IMG_2249.mp4');
            } else {
                abort(404, 'Video not found');
            }
        }

        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($path);
        $response->headers->set('Content-Type', 'video/mp4');
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->prepare($request);

        return $response;
    }
}
