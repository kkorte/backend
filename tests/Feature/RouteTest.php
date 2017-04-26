<?php

namespace Test\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RouteTest extends TestCase
{
    public function testRedirectToLogin()
    {
        $response = $this->get('/hideyo/admin/order-status');
        $response->assertRedirect('/hideyo/admin/security/login');
    }

    public function testLoginIsAvailable()
    {
        $response = $this->get('/hideyo/admin/security/login');
        $response->assertSuccessful();
    }

    public function testApplication()
    {
        $user = $this->_getUser();

        $response = $this->actingAs($user, 'hideyobackend')
                            ->get('/hideyo/admin/order-status');

        $response->assertSuccessful();
    }

    private function _getUser() {
        $result = \Auth::guard('hideyobackend')->attempt([
            'email'    => 'admin@admin.com',
            'password' => 'admin'
        ]);

        if ($result) {
            return \Auth::guard('hideyobackend')->user();
        }

        return factory(\Hideyo\Ecommerce\Backend\Models\User::class)->create([
            'username' => 'admin@admin.com',
            'email'    => 'admin@admin.com',
            'password' => \Hash::make('admin')
        ]);
    }
}
