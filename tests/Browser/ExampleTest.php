<?php

namespace Test\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testShowLoginPage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/hideyo/admin')
            		->assertPathIs('/hideyo/admin/security/login')
                    ->assertTitle('Login')
                    ->type('email', 'admin@admin.com')
                    ->type('password', 'admin')
                    ->press('login')
                    ->assertPathIs('/hideyo/admin');
        });
    }
}
