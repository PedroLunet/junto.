<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Services\FriendService;

class SearchUserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:name_asc,name_desc,date_asc,date_desc'],
        ]);

        $search = $request->input('query', '') ?? "";
        $sort = $request->input('sort', 'name_asc');

        $users = User::query()
            ->where('isdeleted', false)
            ->where('isblocked', false)
            ->when($search, function ($query, $search) {
                return $query->searchByProfile($search);
            });

        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            $currentUser = auth()->user();
            if ($currentUser) {
                $friendIds = $currentUser->friends()->pluck('id')->toArray();
                $users = $users->whereIn('id', $friendIds);
            } else {
                $users = $users->where('id', '<', 0);
            }
        }

        switch ($sort) {
            case 'name_desc':
                $users = $users->orderByNameDesc();
                break;
            case 'date_asc':
                $users = $users->orderByJoinDateAsc();
                break;
            case 'date_desc':
                $users = $users->orderByJoinDateDesc();
                break;
            case 'name_asc':
            default:
                $users = $users->orderByNameAsc();
        }

        $users = $users->get();

        $user = auth()->user();
        if ($user) {
            $friends = $user->friends()->pluck('id')->toArray();
        } else {
            $friends = [];
        }

        $friendService = app(FriendService::class);
        
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'users' => $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                    ];
                })
            ]);
        }
        
        return view("pages.search.index", [
            'users' => $users,
            'friends' => $friends,
            'friendService' => $friendService,
            'sort' => $sort,
        ]);
    }
}
