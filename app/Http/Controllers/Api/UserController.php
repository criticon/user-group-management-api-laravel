<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public $successStatus = 200;

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')-> accessToken;

            return response()->json(['success' => $success], $this-> successStatus);
        }

        return response()->json(['error'=>'Unauthorised'], 401);
    }

    /**
     * Shows a list of all users
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();

        return response()->json(['success' => $users], $this-> successStatus);
    }

    /**
     * Shows user related information
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        if ($user === null) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        return response()->json(['success' => $user], $this-> successStatus);
    }

    /**
     * Creates new user
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')-> accessToken;
        $success['first_name'] =  $user->first_name;
        $success['last_name'] =  $user->last_name;

        return response()->json(['success'=>$success], $this-> successStatus);
    }

    /**
     * Creates new user
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
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
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $input = $request->all();
        $user = User::find($id);
        if ($user === null) {
            return response()->json(['error' => 'Not Found'], 404);
        }
        // Hash password if we got a new one
        if ($request->has('password') and ! Hash::check($input['password'], $user->password)) {
            $input['password'] = bcrypt($input['password']);
        } else {
            $input = array_except($input, ['password', 'c_password']);
        }
        $user->update($input);

        return response()->json(['success'=>$user], $this-> successStatus);
    }
}
