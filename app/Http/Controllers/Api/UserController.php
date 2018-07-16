<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use App\User;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use \Laravel\Passport\Client;
use App\UserGroupManagementApp\Transformers\UserTransformer;

class UserController extends ApiController
{
    /**
     * @var App\UserGroupManagementApp\Transformers\UserTransformer
     */
    protected $userTransformer;

    public function __construct(UserTransformer $userTransformer)
    {
        $this->userTransformer = $userTransformer;
    }
    
    public function getPassportClient()
    {
        return Client::where('password_client', 1)->first();
    }

    /**
     * Gives access token
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $client = $this->getPassportClient();
        $request->request->add([
            'grant_type' => 'password',
            'username' => $request->email,
            'password' => $request->password,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => null
        ]);

        $proxy = Request::create('oauth/token', 'POST');

        return \Route::dispatch($proxy);
    }

    /**
     * Shows a list of all users
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();

        if (! $users) {
            return $this->respondNotFound('No users found.');
        }

        return $this->respondWithData($this->userTransformer->transformCollection($users->all()));
    }

    /**
     * Shows user related information
     *
     * @param int $id user id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return $this->respondNotFound('This user was not found.');
        }

        return $this->respondWithData($this->userTransformer->transform($user));
    }

    /**
     * Creates new user
     *
     * @param \Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        // validate input and return errors in failure
        $validator = Validator::make(
            $input,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users',
                'group_id' => 'exists:groups,id',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]
        );
        if ($validator->fails()) {
            return $this->respondBadRequest($validator->errors());
        }

        // Create user with hashed password
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $user->groups()->attach($input['group_id']);

        $client = $this->getPassportClient();
        $request->request->add([
            'grant_type' => 'password',
            'username' => $input['email'],
            'password' => $request->password, // password shouldn't be hashed here
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => null
        ]);

        $proxy = Request::create('oauth/token', 'POST');

        return \Route::dispatch($proxy);
    }

    /**
     * Creates new user
     *
     * @param \Request $request
     * @param int $id user id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        // validate input and return errors in failure
        $validator = Validator::make(
            $request->all(),
            [
                'first_name' => 'filled',
                'last_name' => 'filled',
                'email' => 'filled|email|unique:users',
                'password' => 'filled',
                'c_password' => 'required_with:password|filled|same:password',
                'state' => [
                    'filled',
                    Rule::in(['active', 'non active']),
                ],
            ]
        );
        if ($validator->fails()) {
            return $this->respondBadRequest($validator->errors());
        }

        /** @var array $input all the input fields */
        $input = $request->all();
        /** @var Model $user model of the user we have to update */
        $user = User::find($id);
        if ($user === null) {
            return $this->respondNotFound('This user was not found.');
        }
        // Hash password if we got a new one
        // or remove it from the $input
        if ($request->has('password') and ! Hash::check($input['password'], $user->password)) {
            $input['password'] = bcrypt($input['password']);
        } else {
            $input = array_except($input, ['password', 'c_password']);
        }
        $user->update($input);

        return $this->respondWithData($this->userTransformer->transform($user));
    }
}
