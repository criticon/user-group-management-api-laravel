<?php

namespace App\Traits;

use App\User;

trait FeatureTestTrait
{

    /**
     * Access token from Laravel Passport
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Creates new user and puts it's access token to this.accessToken
     *
     */
    public function getTestUser()
    {
        $password = '1234';
        $user = User::updateOrCreate([
                "email" => 'test@example.org',
            ],
            [
                "first_name" => 'test',
                "last_name" => 'test',
                "password" => bcrypt($password)
            ]
        );

        return $user;
    }

    public function setAccessToken(User $user)
    {
        $this->accessToken = $user->createToken('mypassportclient')-> accessToken;
    }
}
