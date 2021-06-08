<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StreamsTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/streamAlerts');
        $response = $this->get('/error/streams');
        $response = $this->get('todayEvents');
        $response = $this->get('history');
        $response = $this->get('working/streams');

        $response = $this->post('/streamInfo/status', [
            'streamId' => 1
        ]);

        $response = $this->post('/streamInfo/image', [
            'streamId' => 1
        ]);

        $response = $this->post('/streamInfo/detail', [
            'streamId' => 1
        ]);

        $response = $this->post('/streamInfo/history/10', [
            'streamId' => 1
        ]);

        $response = $this->post('/streamInfo/history/5', [
            'streamId' => 1
        ]);

        $response = $this->post('/streamInfo/doku', [
            'streamId' => 1
        ]);


        $response = $this->post('/streamInfo/ccError', [
            'streamId' => 1
        ]);

        $response = $this->post('/streamInfo/sheduler', [
            'streamId' => 1
        ]);

        $response = $this->post('/streamInfo/todayEvent', [
            'streamId' => 1
        ]);

        $response->assertStatus(200);
    }
}
