<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UsersTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/user');

        $response = $this->get('/users');

        $response = $this->get('/user/roles');

        $response = $this->get('/user/password/generator');

        $response = $this->get('users/get/last/ten');

        $response = $this->get('/user/detail');

        $response->assertStatus(200);
    }
}
