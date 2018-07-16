<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Group;
use Illuminate\Http\Request;
use App\UserGroupManagementApp\Transformers\GroupTransformer;
use Validator;

class GroupsController extends ApiController
{
    /**
     * @var App\UserGroupManagementApp\Transformers\GroupTransformer
     */
    protected $groupTransformer;

    public function __construct(GroupTransformer $groupTransformer)
    {
        $this->groupTransformer = $groupTransformer;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Group::with('users:users.id')->get();

        if (! $groups) {
            return $this->respondNotFound('No groups found.');
        }

        return $this->respondWithData($this->groupTransformer->transformCollection($groups->all()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        // validate input and return errors in failure
        $validator = Validator::make(
            $input,
            [
                'name' => 'required|unique:groups|max:50',
            ]
        );
        if ($validator->fails()) {
            return $this->respondBadRequest($validator->errors());
        }

        // Create group
        $group = Group::create($input);

        return $this->respondWithData($this->groupTransformer->transform($group));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        // validate input and return errors in failure
        $validator = Validator::make(
            $input,
            [
                'users_add' => 'array',
                'users_add.*' => 'distinct|exists:users,id',
                'users_remove' => 'array',
                'users_remove.*' => 'distinct|exists:users,id',
            ]
        );
        if ($validator->fails()) {
            return $this->respondBadRequest($validator->errors());
        }

        $group = Group::find($id);

        if (! $group) {
            return $this->respondNotFound('No groups found.');
        }

        // detach id's
        if ($input['users_remove']) {
            $group->users()->detach($input['users_remove']);
        }

        // attach id's if its doesn't attached
        if ($input['users_add']) {
            $group->users()->syncWithoutDetaching($input['users_add']);
        }

        return $this->respondWithData($this->groupTransformer->transform($group));
    }
}
