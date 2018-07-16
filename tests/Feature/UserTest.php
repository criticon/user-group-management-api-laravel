<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Group;
use App\Traits\FeatureTestTrait;

class UserTest extends TestCase
{

    use WithFaker;
    use FeatureTestTrait;

    public function setUp()
    {
        parent::setUp();

        $this->setAccessToken($this->getTestUser());
    }

    public function testUserIsCreatedCorrectly()
    {
        $password = $this->faker->password();
        $randomGroupId = Group::pluck('id')->random();
        $data = [
            "first_name" => $this->faker->firstName(),
            "last_name" => $this->faker->lastName(),
            "email" => $this->faker->safeEmail(),
            "group_id" => $randomGroupId,
            "password" => $password,
            "c_password" => $password
        ];
        $response = $this->json('POST', '/api/users', $data);
        $response->assertStatus(200);
        $response->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);
    }

    public function testUserIsUpdatedCorrectly()
    {
        /** @var int $randomUserId id of random user which is not test user  */
        $randomUserId = User::where('email', '<>', 'test@example.org')->pluck('id')->random();

        $password = $this->faker->password();
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $email = $this->faker->safeEmail();
        $data = [
            "first_name" => $firstName,
            "last_name" => $lastName,
            "email" => $email,
            "password" => $password,
            "c_password" => $password
        ];

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        $response = $this->json('PUT', sprintf('/api/users/%d', $randomUserId), $data, $headers);
        
        $response->assertStatus(200);
        $response ->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'state',
                'email',
            ],
        ]);
        
        $responseData = json_decode($response->getContent(), true);
        $responseData = array_collapse($responseData);

        // check if data sended equals data received
        $this->assertEquals($randomUserId, $responseData['id']);
        $this->assertEquals($firstName, $responseData['first_name']);
        $this->assertEquals($lastName, $responseData['last_name']);
        $this->assertEquals($email, $responseData['email']);
    }

    public function testUserIsShownCorrectly()
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];
        /** @var int $randomUserId id of random user  */
        $randomUserId = User::pluck('id')->random();
        $response = $this->json('GET', sprintf('/api/users/%d', $randomUserId), [], $headers);

        // check a response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'first_name',
                'last_name',
                'state',
                'email',
            ],
        ]);

        // check if user was authenticated
        $this->assertAuthenticated('api');
    }

    public function testUsersAreListedCorrectly()
    {
        $response = $this->json('GET', '/api/users');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'state',
                        'email',
                    ],
                ],
            ]);
    }
}
