<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FirewallTests extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/firewall/logs');
        $response = $this->get('/firewall/ips');

        $response = $this->post('/firewall/ip/delete', [
            'allowedIPid' => "1.1.1.1"
        ]);

        $response = $this->post('/firewall/ip/create', [
            'ip' => "1.1.1.1"
        ]);

        $response = $this->post('/firewall/settings', [
            'firewallStatus' => false
        ]);

        $response->assertStatus(200);
    }
}
