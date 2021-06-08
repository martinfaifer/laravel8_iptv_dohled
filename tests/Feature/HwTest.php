<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HwTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/system');
        $response = $this->get('/cpu');
        $response = $this->get('/ram');
        $response = $this->get('/swap');
        $response = $this->get('/hdd');
        $response = $this->get('/uptime');
        $response = $this->get('/server/satatus');
        $response = $this->get('/firewall/status');
        $response = $this->get('/certifikate');
        $response = $this->get('/certifikate/check');
        $response = $this->get('/admin/system/info');
        $response = $this->get('/system/avarage/load');

        $response->assertStatus(200);
    }
}
