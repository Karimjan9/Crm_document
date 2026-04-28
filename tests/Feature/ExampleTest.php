<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_home_page_redirects_to_public_site()
    {
        $response = $this->get('/');

        $response
            ->assertStatus(301)
            ->assertHeader('Location', 'https://sites.google.com/view/tarjimalarmarkazi');
    }

    public function test_login_page_returns_a_successful_response()
    {
        $response = $this->get('/login');

        $response->assertOk();
    }

    public function test_maintenance_routes_are_not_available_outside_local_environment()
    {
        $this->get('/clear_cache')->assertNotFound();
        $this->get('/load_script')->assertNotFound();
    }
}
