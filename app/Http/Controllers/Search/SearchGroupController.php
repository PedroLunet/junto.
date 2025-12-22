<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class SearchGroupController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:name_asc,name_desc,members_desc,members_asc'],
            'min_members' => ['nullable', 'integer', 'min:0'],
        ]);

        $search = $request->input('query', '') ?? "";
        $sort = $request->input('sort', 'name_asc');
        $minMembers = $request->input('min_members', 0);

        $groups = Group::query()
            ->withCount('members')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            })
            ->when($minMembers > 0, function ($query) use ($minMembers) {
                return $query->whereRaw('(SELECT COUNT(*) FROM membership WHERE groupid = groups.id) >= ?', [$minMembers]);
            });

        if ($sort === 'name_asc') {
            $groups = $groups->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $groups = $groups->orderBy('name', 'desc');
        } elseif ($sort === 'members_desc') {
            $groups = $groups->orderByRaw('(SELECT COUNT(*) FROM membership WHERE groupid = groups.id) DESC');
        } elseif ($sort === 'members_asc') {
            $groups = $groups->orderByRaw('(SELECT COUNT(*) FROM membership WHERE groupid = groups.id) ASC');
        }

        $groups = $groups->get();

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'groups' => $groups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'isprivate' => $group->isprivate,
                        'members_count' => $group->members_count,
                    ];
                })
            ]);
        }

        return view("pages.search.index", [
            'groups' => $groups,
            'sort' => $sort,
            'activeTab' => 'groups',
        ]);
    }
}
