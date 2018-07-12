<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{
    /**
     * Gives access token
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        // Attempt to login and return access token in success
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $data['token'] =  $user->createToken('MyApp')-> accessToken;

            return $this->respondWithData($data);
        }

        return $this->respondUnauthorised();
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

        return $this->respondWithData($users);
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

        return $this->respondWithData($user);
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
        // validate input and return errors in failure
        $validator = Validator::make(
            $request->all(),
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]
        );
        if ($validator->fails()) {
            return $this->respondBadRequest($validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')-> accessToken;
        $success['first_name'] =  $user->first_name;
        $success['last_name'] =  $user->last_name;

        return $this->respondWithData($data);
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

        return $this->respondWithData($user);
    }
}
