<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\AccommodationType;
use App\Models\AccommodationUnit;
use App\Models\Amenity;
use App\Models\FarmTour;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions
        $permissions = [
            'view_bookings',
            'create_bookings',
            'edit_bookings',
            'cancel_bookings',
            'view_revenue',
            'view_profit',
            'view_expenses',
            'create_sales',
            'refund_sales',
            'adjust_inventory',
            'manage_cms',
            'manage_media',
            'manage_users',
            'manage_roles',
            'view_audit_logs',
            'manage_settings',
        ];

        foreach ($permissions as $p) {
            Permission::create(['name' => $p]);
        }

        // 2. Roles
        $roles = [
            'owner' => $permissions,
            'manager' => [
                'view_bookings', 'create_bookings', 'edit_bookings', 'cancel_bookings',
                'view_revenue', 'view_expenses',
                'create_sales', 'refund_sales', 'adjust_inventory',
                'manage_cms', 'manage_media'
            ],
            'reception' => [
                'view_bookings', 'create_bookings', 'edit_bookings', 'cancel_bookings'
            ],
            'cashier' => [
                'view_bookings', 'create_sales', 'refund_sales'
            ],
            'farm_staff' => [
                'create_sales', 'adjust_inventory'
            ],
            'housekeeping' => [
                'view_bookings'
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }

        // 3. Default Users
        $users = [
            [
                'name' => 'Kitonga Owner',
                'email' => 'owner@kitongafarm.com',
                'phone' => '+255712345678',
                'role' => 'owner',
            ],
            [
                'name' => 'Villa Manager',
                'email' => 'manager@kitongafarm.com',
                'phone' => '+255712345679',
                'role' => 'manager',
            ],
            [
                'name' => 'Front Desk Reception',
                'email' => 'reception@kitongafarm.com',
                'phone' => '+255712345680',
                'role' => 'reception',
            ],
            [
                'name' => 'Main Cashier',
                'email' => 'cashier@kitongafarm.com',
                'phone' => '+255712345681',
                'role' => 'cashier',
            ],
        ];

        foreach ($users as $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'phone' => $u['phone'],
                'active' => true,
                'password' => Hash::make('password'),
            ]);
            $user->assignRole($u['role']);
        }

        // 4. Default Settings
        $settings = [
            'check_in_time' => ['value' => '14:00', 'desc' => 'Default villa check-in time'],
            'check_out_time' => ['value' => '11:00', 'desc' => 'Default villa check-out time'],
            'tax_rate' => ['value' => '18.00', 'desc' => 'VAT percentage in Tanzania'],
            'currency' => ['value' => 'TZS', 'desc' => 'System currency symbol'],
            'cancellation_policy' => ['value' => 'Full refund up to 7 days before check-in. 50% refund 3-7 days. No refund under 3 days.', 'desc' => 'Default booking cancellation policy description'],
            'deposit_percentage' => ['value' => '50.00', 'desc' => 'Deposit percentage required to confirm a booking'],
            'contact_email' => ['value' => 'kitongafarmvillas@gmail.com', 'desc' => 'Public contact email address'],
            'contact_phone' => ['value' => '+255 784 123 456', 'desc' => 'Public contact phone/WhatsApp number'],
            'location_coordinates' => ['value' => '-5.15833,39.06222', 'desc' => 'Google Map pin coordinates for the farm villas'],
            'breakfast_policy' => ['value' => 'Complimentary premium farm breakfast included for all occupants.', 'desc' => 'Breakfast terms of stay'],
        ];

        foreach ($settings as $key => $data) {
            Setting::set($key, $data['value'], $data['desc']);
        }

        // 5. Customers
        $customers = [
            ['name' => 'Juma Shabaan', 'phone' => '+255754998877', 'email' => 'juma@example.com', 'notes' => 'Frequent local guest, prefers quiet semi-luxury villa.'],
            ['name' => 'Sarah Jenkins', 'phone' => '+1415998877', 'email' => 'sarah.j@example.com', 'notes' => 'International traveler, interested in dairy farm tour.'],
            ['name' => 'Mariam Mchome', 'phone' => '+255655121212', 'email' => 'mariam@example.com', 'notes' => 'Family group booking.'],
        ];
        foreach ($customers as $c) {
            Customer::create($c);
        }

        // 6. Amenities
        $amenities = [
            'Free Wi-Fi' => 'wifi',
            'DSTV' => 'tv',
            'Azam TV' => 'tv',
            'Interior Kitchen' => 'kitchen',
            'Swimming Pool' => 'pool',
            'King Size Bed' => 'bed',
            'Air Conditioning' => 'ac',
            'Private Terrace' => 'terrace',
        ];
        $amenityModels = [];
        foreach ($amenities as $name => $icon) {
            $amenityModels[$name] = Amenity::create([
                'name' => $name,
                'icon' => $icon,
            ]);
        }

        // 7. Accommodation Types & Units
        $villas = [
            [
                'name' => 'Luxury Villa',
                'slug' => 'luxury-villa',
                'description' => 'Experience premium luxury surrounded by peaceful farm landscape. Features spacious open veranda, private bathroom, and direct access to swimming pool.',
                'short_description' => 'The ultimate luxury country escape.',
                'base_price' => 250000.00,
                'capacity' => 2,
                'bedrooms' => 1,
                'beds' => 1,
                'bathrooms' => 1,
                'has_interior_kitchen' => false,
                'featured_image' => 'luxury_villa_img.jpg',
                'amenities' => ['Free Wi-Fi', 'DSTV', 'Azam TV', 'Swimming Pool', 'King Size Bed', 'Air Conditioning', 'Private Terrace'],
                'units' => ['V1 - Luxury Villa 1', 'V2 - Luxury Villa 2'],
            ],
            [
                'name' => 'Semi Luxury Villa',
                'slug' => 'semi-luxury-villa',
                'description' => 'Comfortable and private escape featuring standard premium amenities, beautiful garden view and access to common farm gardens and pool.',
                'short_description' => 'Perfect balance of comfort and farm experience.',
                'base_price' => 200000.00,
                'capacity' => 2,
                'bedrooms' => 1,
                'beds' => 1,
                'bathrooms' => 1,
                'has_interior_kitchen' => false,
                'featured_image' => 'semi_luxury_villa_img.jpg',
                'amenities' => ['Free Wi-Fi', 'Azam TV', 'Swimming Pool', 'King Size Bed', 'Air Conditioning'],
                'units' => ['S1 - Semi Luxury 1', 'S2 - Semi Luxury 2'],
            ],
            [
                'name' => 'Family Villa',
                'slug' => 'family-villa',
                'description' => 'Spacious 2-bedroom house with an interior kitchen, large private dining area, and dedicated parking. Ideal for families and small groups wanting home-cooked farm food.',
                'short_description' => '2 Bedrooms + Interior Kitchen.',
                'base_price' => 400000.00,
                'capacity' => 6,
                'bedrooms' => 2,
                'beds' => 3,
                'bathrooms' => 2,
                'has_interior_kitchen' => true,
                'featured_image' => 'family_villa_img.jpg',
                'amenities' => ['Free Wi-Fi', 'DSTV', 'Azam TV', 'Interior Kitchen', 'Swimming Pool', 'Air Conditioning', 'Private Terrace'],
                'units' => ['F1 - Family House 1'],
            ],
        ];

        foreach ($villas as $v) {
            $type = AccommodationType::create([
                'name' => $v['name'],
                'slug' => $v['slug'],
                'description' => $v['description'],
                'short_description' => $v['short_description'],
                'base_price' => $v['base_price'],
                'capacity' => $v['capacity'],
                'bedrooms' => $v['bedrooms'],
                'beds' => $v['beds'],
                'bathrooms' => $v['bathrooms'],
                'has_interior_kitchen' => $v['has_interior_kitchen'],
                'featured_image' => $v['featured_image'],
                'gallery_images' => [],
                'active' => true,
                'sort_order' => 0,
            ]);

            // Sync amenities
            $amenityIds = [];
            foreach ($v['amenities'] as $aName) {
                if (isset($amenityModels[$aName])) {
                    $amenityIds[] = $amenityModels[$aName]->id;
                }
            }
            $type->amenities()->sync($amenityIds);

            // Create physical units
            foreach ($v['units'] as $uName) {
                AccommodationUnit::create([
                    'accommodation_type_id' => $type->id,
                    'name' => $uName,
                    'status' => 'active',
                ]);
            }
        }

        // 8. Farm Tours
        $tours = [
            [
                'name' => 'Normal Farm Tour',
                'slug' => 'normal-farm-tour',
                'description' => 'A relaxed, guided entry into the rhythmic beauty of Kitonga. Wander through central palm pathways, observe seasonal fruit plantations, and understand our farming philosophy before cooling off in our rural farm bar and pool lounge.',
                'price' => 20000.00,
                'capacity_per_slot' => 30,
                'category' => 'Nature & Trails',
                'duration' => '2 Hours',
                'featured_image' => 'normal_farm_tour.jpg',
                'gallery' => [
                    'normal_farm_tour.jpg',
                    'gallery_img_0217.jpg',
                    'gallery_img_0216.jpg',
                    'gallery_img_0223.jpg',
                    'gallery_img_0220.jpg',
                    'farm_gallery_1.jpg',
                ],
                'inclusions' => [
                    'Guided farm path tour',
                    'Fresh coconut refreshments',
                    'Access to the swimming pool',
                    'A tour of the central mango orchard'
                ],
                'highlights' => [
                    'Vibrant papaya and organic chilli fields',
                    'Relaxing countryside swimming pool',
                    'Pure fresh-picked coconut juice straight from our palms'
                ],
                'good_to_know' => 'Wear comfortable closed walking shoes, a sun hat, and bring your swimwear and towel.',
                'featured' => true,
                'sort_order' => 1,
                'status' => 'published',
                'seo_title' => 'Normal Farm Tour - Authentic Guided Tour',
                'seo_description' => 'Tour the central farm paths, crop areas (mango, papaya, chilli) and finish with a refreshing swim in our pool.',
            ],
            [
                'name' => 'General Farm Tour',
                'slug' => 'general-farm-tour',
                'description' => 'Our complete agritourism experience. Dive deep into all operational aspects of Kitonga Farm: visit vanilla and strawberry greenhouses, interact with dairy and poultry livestock, and taste farm-fresh organic items right from the soil.',
                'price' => 50000.00,
                'capacity_per_slot' => 15,
                'category' => 'Complete Ecosystem',
                'duration' => '4 Hours',
                'featured_image' => 'general_farm_hero.jpg',
                'gallery' => [
                    'general_farm_hero.jpg',
                    'three_cows.jpg',
                    'download_41.jpg',
                    'download_40.jpg',
                ],
                'inclusions' => [
                    'Complete farm tour covering all zones',
                    'Greenhouses and vanilla farms admission',
                    'Livestock interaction (cattle, goats, poultry)',
                    'Mini-bar beverage & farm yogurt tasting',
                    'Swimming pool access'
                ],
                'highlights' => [
                    'High-tech vanilla and strawberry greenhouses',
                    'Interacting with dairy cows and poultry birds',
                    'Local yogurt and milk tasting session'
                ],
                'good_to_know' => 'Perfect for families and agricultural enthusiasts. Includes tasting items, but please advise us on any food allergies before arrival.',
                'featured' => true,
                'sort_order' => 2,
                'status' => 'published',
                'seo_title' => 'General Farm Tour - Immersive Experience',
                'seo_description' => 'Detailed wider tour including livestock sections (dairy/cattle, goat farm, poultry houses: chicken, turkeys, ducks) plus vanilla and strawberry greenhouses.',
            ]
        ];
        foreach ($tours as $t) {
            FarmTour::create($t);
        }

        // 9. Product Categories
        $categories = [
            'farm_produce' => 'Farm Produce',
            'mini_bar' => 'Mini Bar',
        ];
        $categoryModels = [];
        foreach ($categories as $slug => $name) {
            $categoryModels[$slug] = ProductCategory::create([
                'name' => $name,
                'slug' => $slug,
            ]);
        }

        // 10. Products
        $products = [
            [
                'product_category_id' => 'farm_produce',
                'sku' => 'KFV-P-MANGO',
                'name' => 'Fresh Kitonga Mangoes',
                'description' => 'Organic sweet mangoes harvested directly from our fields.',
                'unit' => 'kg',
                'selling_price' => 3000.00,
                'cost_price' => 1000.00,
                'stock' => 120,
            ],
            [
                'product_category_id' => 'farm_produce',
                'sku' => 'KFV-P-EGGS',
                'name' => 'Farm Fresh Eggs',
                'description' => 'One tray of farm eggs from free-range chickens.',
                'unit' => 'tray',
                'selling_price' => 10000.00,
                'cost_price' => 6000.00,
                'stock' => 45,
            ],
            [
                'product_category_id' => 'farm_produce',
                'sku' => 'KFV-P-MILK',
                'name' => 'Dairy Milk (Fresh)',
                'description' => 'Unpasteurized fresh dairy milk from our cattle.',
                'unit' => 'liter',
                'selling_price' => 2500.00,
                'cost_price' => 1000.00,
                'stock' => 60,
            ],
            [
                'product_category_id' => 'farm_produce',
                'sku' => 'KFV-P-HONEY',
                'name' => 'Organic Farm Honey',
                'description' => 'Raw, pure forest honey harvested from our bee farm.',
                'unit' => 'bottle (500ml)',
                'selling_price' => 12000.00,
                'cost_price' => 5000.00,
                'stock' => 20,
            ],
            [
                'product_category_id' => 'mini_bar',
                'sku' => 'KFV-MB-COFFEE',
                'name' => 'Kitonga Blend Coffee',
                'description' => 'Freshly ground local coffee served hot.',
                'unit' => 'cup',
                'selling_price' => 3000.00,
                'cost_price' => 800.00,
                'stock' => 200,
            ],
            [
                'product_category_id' => 'mini_bar',
                'sku' => 'KFV-MB-SODA',
                'name' => 'Soda (Coca-Cola / Fanta / Sprite)',
                'description' => 'Cold carbonated drinks.',
                'unit' => 'bottle',
                'selling_price' => 2000.00,
                'cost_price' => 1200.00,
                'stock' => 80,
            ]
        ];

        foreach ($products as $p) {
            $product = Product::create([
                'product_category_id' => $categoryModels[$p['product_category_id']]->id,
                'sku' => $p['sku'],
                'name' => $p['name'],
                'description' => $p['description'],
                'unit' => $p['unit'],
                'selling_price' => $p['selling_price'],
                'cost_price' => $p['cost_price'],
                'stock' => $p['stock'],
                'low_stock_threshold' => 10,
                'active' => true,
            ]);

            // Create an initial stock movement
            InventoryMovement::create([
                'product_id' => $product->id,
                'type' => 'opening',
                'quantity' => $p['stock'],
                'reference_type' => 'manual',
                'reason' => 'Initial stock seeding',
            ]);
        }

        // 11. CMS Content Seeding
        // Homepage
        $homePage = \App\Models\CmsPage::create([
            'title' => 'Homepage',
            'slug' => 'home',
            'seo_title' => 'Where Luxury Meets Farm Life',
            'seo_description' => 'Premium countryside accommodation and farm stay in Komkonga, Tanga.',
            'active' => true,
        ]);

        \App\Models\CmsSection::create([
            'cms_page_id' => $homePage->id,
            'key' => 'hero_headline',
            'type' => 'text',
            'value' => 'Where Luxury Meets Farm Life',
        ]);

        \App\Models\CmsSection::create([
            'cms_page_id' => $homePage->id,
            'key' => 'hero_subheadline',
            'type' => 'text',
            'value' => 'Escape to authentic countryside serenity in Komkonga Village. Indulge in private villas, pure organic food, and premium nature tours.',
        ]);

        \App\Models\CmsSection::create([
            'cms_page_id' => $homePage->id,
            'key' => 'brand_story',
            'type' => 'text',
            'value' => 'Kitonga Farm Villas is a luxury countryside destination in Komkonga, Tanga. We connect high-end villa hospitality with organic farming—offering fresh fruits, swimming pool, forest trails, and cattle farm experiences.',
        ]);

        // Farm Page
        $farmPage = \App\Models\CmsPage::create([
            'title' => 'Our Farm',
            'slug' => 'farm',
            'seo_title' => 'Organic Agriculture & Farm-to-Table',
            'seo_description' => 'Ecology guidelines and cattle feeding models in Tanga.',
            'active' => true,
        ]);

        \App\Models\CmsSection::create([
            'cms_page_id' => $farmPage->id,
            'key' => 'farm_story',
            'type' => 'text',
            'value' => 'Kitonga Farm Villas is built on a 50-acre organic reserve dedicated to agroecology. We cultivate arabica coffee, harvest raw honey, and raise dairy cows, all while maintaining absolute preservation of the native countryside flora and fauna.',
        ]);

        // Experiences Page
        $expPage = \App\Models\CmsPage::create([
            'title' => 'Experiences & Tours',
            'slug' => 'experiences',
            'seo_title' => 'Farm Activities & Walks',
            'seo_description' => 'Guided farm walks and cattle milking experiences in Tanga.',
            'active' => true,
        ]);

        \App\Models\CmsSection::create([
            'cms_page_id' => $expPage->id,
            'key' => 'experiences_intro',
            'type' => 'text',
            'value' => 'Immerse yourself in native agrarian lifestyles and explore the Tanga landscape with our guides.',
        ]);

        // About Page
        $aboutPage = \App\Models\CmsPage::create([
            'title' => 'About Us',
            'slug' => 'about',
            'seo_title' => 'The Story of Kitonga Farm Villas',
            'seo_description' => 'Our history, values, and community impact.',
            'active' => true,
        ]);

        \App\Models\CmsSection::create([
            'cms_page_id' => $aboutPage->id,
            'key' => 'brand_story',
            'type' => 'text',
            'value' => 'Kitonga Farm Villas was founded on the philosophy that true luxury is found in nature, silence, and clean, organic living. Tucked away in Komkonga Village within the Tanga Region, our farm-resort offers a pure, unhurried escape from modern stress. We operate on ecological farming guidelines, powering our operations with sustainability and supporting local village families.',
        ]);

        // Gallery Page
        $galleryPage = \App\Models\CmsPage::create([
            'title' => 'Gallery',
            'slug' => 'gallery',
            'seo_title' => 'Visual Gallery - Kitonga Farm Villas',
            'seo_description' => 'Photos of our luxury villas and organic farms.',
            'active' => true,
        ]);

        // Policies Page
        $policiesPage = \App\Models\CmsPage::create([
            'title' => 'Policies',
            'slug' => 'policies',
            'seo_title' => 'Booking Policies & Refund Rules',
            'seo_description' => 'Read our cancellation and stay terms.',
            'active' => true,
        ]);

        // 12. Expense Categories & Sample Expenses Seeding
        $farmOps = \App\Models\ExpenseCategory::create([
            'name' => 'Farm Operations',
            'description' => 'Seeds, livestock feed, fertilizer, and general agriculture inputs.',
        ]);

        $resortUtil = \App\Models\ExpenseCategory::create([
            'name' => 'Resort Utilities',
            'description' => 'Electricity, water supply, Azam TV renewals, and laundry gas.',
        ]);

        $payroll = \App\Models\ExpenseCategory::create([
            'name' => 'Staff Payroll',
            'description' => 'Salaries, overtime, and kitchen crew allowances.',
        ]);

        \App\Models\Expense::create([
            'category' => 'farm',
            'expense_category_id' => $farmOps->id,
            'amount' => 45000.00,
            'date' => now()->subDays(5),
            'description' => 'Purchase of Organic Fertilizers: 10 bags of organic manure for greenhouses.',
            'payment_method' => 'mobile_money',
            'status' => 'approved',
            'created_by' => 1,
        ]);

        \App\Models\Expense::create([
            'category' => 'resort',
            'expense_category_id' => $resortUtil->id,
            'amount' => 12000.00,
            'date' => now()->subDays(2),
            'description' => 'Azam TV Resort subscription: Monthly subscription for 5 villa units.',
            'payment_method' => 'cash',
            'status' => 'approved',
            'created_by' => 1,
        ]);
    }
}
