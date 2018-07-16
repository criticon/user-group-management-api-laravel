<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Group;
use App\User;
use App\Traits\FeatureTestTrait;

class GroupTest extends TestCase
{

    use WithFaker;
    use FeatureTestTrait;

    public function setUp()
    {
        parent::setUp();

        $this->setAccessToken($this->getTestUser());
    }

    public function testGroupsAreListedCorrectly()
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];
        $response = $this->json('GET', '/api/groups', [], $headers);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'users'
                    ],
                ],
            ]);
    }

    public function testGroupIsCreatedCorrectly()
    {
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];
        $data = [
            "name" => $this->faker->text(50),
        ];
        $response = $this->json('POST', '/api/groups', $data, $headers);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'users'
            ],
        ]);
    }

    public function testGroupIsUpdatedCorrectly()
    {
        // prepare query
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];
        /** @var array $toAddIds id's of users which needs to be added to the group  */
        $toAddIds = User::where('email', '<>', 'test@example.org')->pluck('id')->random(3)->all();
        /** @var array $toRemoveIds id's of users which needs to be removed from the group */
        $toRemoveIds = User::where('email', '<>', 'test@example.org')->pluck('id')->random(3)->all();
        $data = [
            'users_add' => $toAddIds,
            'users_remove' => $toRemoveIds
        ];
        /** @var int $randomGroupId id of a random group  */
        $randomGroupId = Group::pluck('id')->random();

        /** @var array $beforeUpdatedUserIds ids of users which had been attached to the group before changes have been made */
        $beforeIds = Group::find($randomGroupId)->users()->pluck('users.id')->all();

        // make query
        $response = $this->json('PUT', sprintf('/api/groups/%d', $randomGroupId), $data, $headers);
        
        // determine expected user ids related to the group after update has been made
        $afterExpectedIds = array_diff(array_unique(array_merge($beforeIds, $toAddIds)), $toRemoveIds);
        // determine user ids that present in pivot table for the group
        $afterRealIds = Group::find($randomGroupId)->users()->pluck('users.id')->all();
        // check if changes have been made
        $this->assertEquals($afterExpectedIds, $afterRealIds);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'users'
                ],
            ]);
    }
}
