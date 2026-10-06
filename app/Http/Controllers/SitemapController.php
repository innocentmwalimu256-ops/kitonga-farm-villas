<?php

namespace App\Http\Controllers;

use App\Models\AccommodationType;
use App\Models\FarmTour;
use App\Models\CmsPage;
use Carbon\Carbon;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml for search engines.
     */
    public function index(): Response
    {
        $baseUrl = config('app.url', 'https://kitongafarm.com');
        if (!str_starts_with($baseUrl, 'http')) {
            $baseUrl = 'https://' . ltrim($baseUrl, '/');
        }
        $baseUrl = rtrim($baseUrl, '/');

        // 1. Static Public Pages
        $pages = [
            [
                'loc' => "{$baseUrl}/",
                'lastmod' => Carbon::now()->startOfDay()->toIso8601String(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => "{$baseUrl}/villas",
                'lastmod' => Carbon::now()->subDays(2)->startOfDay()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => "{$baseUrl}/experiences",
                'lastmod' => Carbon::now()->subDays(2)->startOfDay()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ],
            [
                'loc' => "{$baseUrl}/farm",
                'lastmod' => Carbon::now()->subDays(5)->startOfDay()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => "{$baseUrl}/products",
                'lastmod' => Carbon::now()->subDays(1)->startOfDay()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => "{$baseUrl}/gallery",
                'lastmod' => Carbon::now()->subDays(5)->startOfDay()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.75',
            ],
            [
                'loc' => "{$baseUrl}/about",
                'lastmod' => Carbon::now()->subDays(10)->startOfDay()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => "{$baseUrl}/location",
                'lastmod' => Carbon::now()->subDays(10)->startOfDay()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => "{$baseUrl}/contact",
                'lastmod' => Carbon::now()->subDays(3)->startOfDay()->toIso8601String(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
            [
                'loc' => "{$baseUrl}/book",
                'lastmod' => Carbon::now()->startOfDay()->toIso8601String(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => "{$baseUrl}/policies",
                'lastmod' => Carbon::now()->subDays(20)->startOfDay()->toIso8601String(),
                'changefreq' => 'yearly',
                'priority' => '0.5',
            ],
        ];

        // 2. Active Villa Accommodation Types
        try {
            $villas = AccommodationType::where('active', true)->get();
            foreach ($villas as $villa) {
                $pages[] = [
                    'loc' => "{$baseUrl}/villas/{$villa->slug}",
                    'lastmod' => $villa->updated_at ? $villa->updated_at->toIso8601String() : Carbon::now()->subDays(3)->toIso8601String(),
                    'changefreq' => 'weekly',
                    'priority' => '0.9',
                ];
            }
        } catch (\Exception $e) {
            // fallback
        }

        // 3. Published Farm Experiences & Tours
        try {
            $experiences = FarmTour::where('status', 'published')->orWhere('active', true)->get();
            foreach ($experiences as $exp) {
                if ($exp->slug) {
                    $pages[] = [
                        'loc' => "{$baseUrl}/experiences/{$exp->slug}",
                        'lastmod' => $exp->updated_at ? $exp->updated_at->toIso8601String() : Carbon::now()->subDays(4)->toIso8601String(),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                    ];
                }
            }
        } catch (\Exception $e) {
            // fallback
        }

        // Generate XML output
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($pages as $p) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($p['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>{$p['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$p['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$p['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600, s-maxage=86400',
        ]);
    }

    /**
     * Generate dynamic robots.txt file.
     */
    public function robots(): Response
    {
        $baseUrl = config('app.url', 'https://kitongafarm.com');
        $baseUrl = rtrim($baseUrl, '/');

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /profile\n";
        $content .= "Disallow: /preview-admin-dashboard\n";
        $content .= "Disallow: /booking/receipt/\n";
        $content .= "Disallow: /booking/success/\n";
        $content .= "Disallow: /login\n";
        $content .= "Disallow: /register\n";
        $content .= "Disallow: /password/\n";
        $content .= "\n";
        $content .= "Sitemap: {$baseUrl}/sitemap.xml\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
