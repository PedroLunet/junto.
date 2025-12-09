<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::all();

        return view('pages.groups.list', ['groups' => $groups]);
    }

    public function show(Group $group)
    {
        return view('pages.groups.show', ['group' => $group]);
    }

    public function create()
    {
        return view('pages.groups.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'isPrivate' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('groups.create')
                ->withErrors($validator)
                ->withInput();
        }

        $group = new Group($request->only(['name', 'description', 'isPrivate']));
        $group->save();

        $group->members()->attach(Auth::id(), ['isOwner' => true]);

        return redirect()->route('groups.show', $group);
    }

    public function join(Group $group)
    {
        $group->members()->attach(Auth::id());

        return back();
    }

    public function leave(Group $group)
    {
        $group->members()->detach(Auth::id());

        return back();
    }
}
