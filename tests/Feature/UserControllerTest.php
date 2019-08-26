<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => "Omotola Awokunle",
            'business_name' => 'Omotola TechWorld', 'address' => 'Iwo Road', 'city' => 'Ibadan',
            'state' => 'Oyo', 'phone' => '08101075316', 'service' => 'App Development', 'password' => 'omotola99'
        ]);
        $token = $this->user->createToken('raxon')->accessToken;
        $this->headers['Accept'] = 'application/json';
        $this->headers['Authorization'] = 'Bearer ' . $token;
    }

    /**
     * Test register functionality
     *
     * @return void
     */
    public function testRegister()
    {
        $response = $this->post('/api/register', [
            'name' => "Opeyemi Olabode",
            'business_name' => 'AppZone', 'address' => 'Alakia', 'city' => 'Ibadan',
            'state' => 'Oyo', 'phone' => '08120443965', 'service' => 'Mobile App Development', 'password' => 'olabode22'
        ]);
        $response->assertStatus(200);
        $response->assertJson(['user', 'message', 'token']);
    }

    /**
     * Test login functionality
     *
     * @return void
     */
    public function testLogin()
    {
        $response = $this->post('/api/login', ['phone' => "08101075316", 'password' => 'omotola99']);
        $response->assertStatus(200);
        $response->assertJson(['user', 'message', 'token']);
    }

    /**
     * Test search functionality
     *
     * @return void
     */
    public function testSearch()
    {
        $response = $this->post('/api/search', ['search' => "App Development", 'location' => 'Ibadan']);
        $response->assertStatus(200);
        $response->assertJson(['results']);
    }
    /**
     * Test verification functionality
     *
     * @return void
     */
    public function testVerification()
    {
        $response = $this->post('/api/phone/verify', ['code' => $this->user->verification_code], $this->headers);
        $response->assertStatus(200);
        $response->assertJson(['message', 'user']);
    }

    /**
     * Test profile functionality
     *
     * @return void
     */
    public function testProfile()
    {
        $response = $this->get('/api/profile', $this->headers);
        $response->assertStatus(200);
        $response->assertJson(['user']);
    }
}
