<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RBACSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic permissions and roles
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

        $owner = Role::create(['name' => 'owner']);
        $owner->givePermissionTo($permissions);

        $housekeeping = Role::create(['name' => 'housekeeping']);
        $housekeeping->givePermissionTo(['view_bookings']);
    }

    /**
     * Test unauthorized access to restricted endpoints.
     */
    public function test_unauthorized_user_is_denied_access()
    {
        $unauthorizedUser = User::create([
            'name' => 'Housekeeper',
            'email' => 'house@test.com',
            'password' => bcrypt('password'),
        ]);
        $unauthorizedUser->assignRole('housekeeping');

        // Housekeeper does not have 'manage_settings'
        $response = $this->actingAs($unauthorizedUser)->get(route('admin.settings.index'));
        $response->assertStatus(403);

        // Housekeeper does not have 'manage_users'
        $response = $this->actingAs($unauthorizedUser)->get(route('admin.users.index'));
        $response->assertStatus(403);

        // Housekeeper does not have 'view_profit' / 'view_revenue'
        $response = $this->actingAs($unauthorizedUser)->get(route('admin.financials.report'));
        $response->assertStatus(403);
    }

    /**
     * Test authorized access to restricted endpoints.
     */
    public function test_authorized_user_can_access()
    {
        $authorizedUser = User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
        ]);
        $authorizedUser->assignRole('owner');

        $response = $this->actingAs($authorizedUser)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($authorizedUser)->get(route('admin.settings.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($authorizedUser)->get(route('admin.users.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($authorizedUser)->get(route('admin.financials.report'));
        $response->assertStatus(200);
    }
}
