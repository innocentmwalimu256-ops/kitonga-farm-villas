<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Customer;
use App\Models\AccommodationType;
use App\Models\AccommodationUnit;
use App\Models\Booking;
use App\Models\Setting;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Exception;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected $bookingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bookingService = new BookingService();

        // Seed basic settings needed for tax/cancellation
        Setting::set('tax_rate', '18.00');
        Setting::set('currency', 'TZS');

        // Create standard roles
        Role::create(['name' => 'owner']);
    }

    /**
     * Test successful booking creation and overlapping double booking prevention.
     */
    public function test_booking_creation_and_double_booking_prevention()
    {
        // 1. Create a single accommodation type and exactly one unit
        $villaType = AccommodationType::create([
            'name' => 'Luxury Villa',
            'slug' => 'luxury-villa',
            'base_price' => 250000.00,
            'capacity' => 2,
            'active' => true,
        ]);

        $unit = AccommodationUnit::create([
            'accommodation_type_id' => $villaType->id,
            'name' => 'Villa V1',
            'status' => 'active',
        ]);

        // 2. Create first booking (Sept 1 to Sept 5)
        $bookingData1 = [
            'accommodation_type_id' => $villaType->id,
            'customer_name' => 'Juma Shabaan',
            'customer_email' => 'juma@example.com',
            'check_in' => '2026-09-01',
            'check_out' => '2026-09-05',
            'guests_count' => 2,
            'source' => 'online',
        ];

        $booking1 = $this->bookingService->createBooking($bookingData1);
        $this->assertNotNull($booking1);
        $this->assertEquals('Villa V1', $booking1->unit->name);
        $this->assertEquals(4, $booking1->duration_in_nights);
        
        // Price check: (250000 * 4) = 1,000,000. Tax is 18% = 180,000. Total = 1,180,000.
        $this->assertEquals(1180000.00, $booking1->total);

        // 3. Try to book another booking that overlaps (Sept 3 to Sept 7)
        // Since there is only one physical unit "Villa V1", this should fail.
        $bookingData2 = [
            'accommodation_type_id' => $villaType->id,
            'customer_name' => 'Sarah Jenkins',
            'customer_email' => 'sarah@example.com',
            'check_in' => '2026-09-03',
            'check_out' => '2026-09-07',
            'guests_count' => 1,
            'source' => 'walk_in',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No units of type 'Luxury Villa' are available for the selected dates.");
        
        $this->bookingService->createBooking($bookingData2);
    }

    /**
     * Test check-in on departure day is allowed (no double-booking).
     */
    public function test_booking_check_in_on_same_day_as_previous_check_out_succeeds()
    {
        $villaType = AccommodationType::create([
            'name' => 'Luxury Villa',
            'slug' => 'luxury-villa',
            'base_price' => 250000.00,
            'capacity' => 2,
            'active' => true,
        ]);

        $unit = AccommodationUnit::create([
            'accommodation_type_id' => $villaType->id,
            'name' => 'Villa V1',
            'status' => 'active',
        ]);

        // Booking 1: Sept 1 to Sept 5
        $booking1 = $this->bookingService->createBooking([
            'accommodation_type_id' => $villaType->id,
            'customer_name' => 'Guest One',
            'check_in' => '2026-09-01',
            'check_out' => '2026-09-05',
            'guests_count' => 2,
        ]);

        // Booking 2: Sept 5 to Sept 10 (starts exactly on the day Booking 1 ends)
        $booking2 = $this->bookingService->createBooking([
            'accommodation_type_id' => $villaType->id,
            'customer_name' => 'Guest Two',
            'check_in' => '2026-09-05',
            'check_out' => '2026-09-10',
            'guests_count' => 1,
        ]);

        $this->assertNotNull($booking2);
        $this->assertEquals('Villa V1', $booking2->unit->name);
    }

    /**
     * Test booking preserves historical prices when villa base price changes.
     */
    public function test_booking_price_historical_preservation()
    {
        $villaType = AccommodationType::create([
            'name' => 'Luxury Villa',
            'slug' => 'luxury-villa',
            'base_price' => 250000.00,
            'capacity' => 2,
            'active' => true,
        ]);

        $unit = AccommodationUnit::create([
            'accommodation_type_id' => $villaType->id,
            'name' => 'Villa V1',
            'status' => 'active',
        ]);

        // Book when rate is 250,000
        $booking = $this->bookingService->createBooking([
            'accommodation_type_id' => $villaType->id,
            'customer_name' => 'Guest One',
            'check_in' => '2026-09-01',
            'check_out' => '2026-09-03', // 2 nights
            'guests_count' => 2,
        ]);

        $this->assertEquals(590000.00, $booking->total); // (250000*2) * 1.18 = 590000

        // Change the villa base price to 300,000
        $villaType->update(['base_price' => 300000.00]);

        // Reload the booking total and verify it did NOT change!
        $booking = $booking->fresh();
        $this->assertEquals(590000.00, $booking->total);
    }

    /**
     * Test booking with farm tour extras and guest registrations.
     */
    public function test_booking_with_extras_and_guest_registration()
    {
        $villaType = AccommodationType::create([
            'name' => 'Luxury Villa',
            'slug' => 'luxury-villa',
            'base_price' => 250000.00,
            'capacity' => 2,
            'active' => true,
        ]);

        $unit = AccommodationUnit::create([
            'accommodation_type_id' => $villaType->id,
            'name' => 'Villa V1',
            'status' => 'active',
        ]);

        $tour = \App\Models\FarmTour::create([
            'name' => 'Sunset Safari Tour',
            'slug' => 'sunset-safari-tour',
            'description' => 'Beautiful farm sunset safari',
            'price' => 50000.00,
            'active' => true,
        ]);

        // Create booking with one tour extra
        $booking = $this->bookingService->createBooking([
            'accommodation_type_id' => $villaType->id,
            'customer_name' => 'Guest Extras Test',
            'check_in' => '2026-09-01',
            'check_out' => '2026-09-03', // 2 nights (500,000 villa)
            'guests_count' => 2,
            'extras' => [
                [
                    'type' => 'tour',
                    'id' => $tour->id,
                    'quantity' => 1,
                ]
            ]
        ]);

        // Subtotal: 500,000 (accommodation) + 50,000 (tour) = 550,000
        // Total: 550,000 * 1.18 = 649,000
        $this->assertEquals(550000.00, $booking->subtotal);
        $this->assertEquals(649000.00, $booking->total);

        // Verify primary guest was registered in the booking_guests table
        $this->assertDatabaseHas('booking_guests', [
            'booking_id' => $booking->id,
            'full_name' => 'Guest Extras Test',
            'is_primary' => true,
        ]);
    }

    /**
     * Test direct standalone day tour / experience booking without requiring any villa accommodation.
     */
    public function test_direct_experience_tour_booking_succeeds_without_accommodation()
    {
        $tour = \App\Models\FarmTour::create([
            'name' => 'Normal Farm Tour',
            'slug' => 'normal-farm-tour-direct-test',
            'description' => 'Guided farm path and orchard tour',
            'price' => 20000.00,
            'capacity_per_slot' => 30,
            'duration' => '2 Hours',
            'status' => 'published',
            'active' => true,
        ]);

        $response = $this->postJson(route('experiences.book'), [
            'farm_tour_id' => $tour->id,
            'tour_date' => '2026-09-10',
            'time_slot' => '09:00 AM - 11:00 AM',
            'guests_count' => 3,
            'customer_name' => 'Neema Emmanuel',
            'customer_phone' => '+255744718614',
            'customer_email' => 'neema@example.com',
            'payment_method' => 'arrival',
            'notes' => 'Looking forward to the farm visit!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'booking' => [
                'guests' => 3,
                'total' => 60000.00,
                'customer_name' => 'Neema Emmanuel',
                'customer_phone' => '+255744718614',
                'payment_method' => 'arrival',
                'status' => 'confirmed',
            ]
        ]);

        // Verify booking in database has null accommodation_unit_id
        $this->assertDatabaseHas('bookings', [
            'customer_id' => \App\Models\Customer::where('phone', '+255744718614')->first()->id,
            'accommodation_unit_id' => null,
            'guests_count' => 3,
            'total' => 60000.00,
            'status' => 'confirmed',
        ]);
    }
}
