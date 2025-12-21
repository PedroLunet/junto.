<?php

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Models\User\User;

use Illuminate\Http\Request;
use App\Services\FriendService;
use function Laravel\Prompts\search;

class SearchUserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => ['nullable', 'string', 'max:255'],
        ]);

        $search = $request->input('query', '') ?? "";

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("fts_document @@ plainto_tsquery('english', ?)", [$search])
                      ->orWhere('username', 'ILIKE', "%$search%")
                      ->orWhere('name', 'ILIKE', "%$search%");
                })
                ->orderByRaw("ts_rank(fts_document, plainto_tsquery('english', ?)) DESC NULLS LAST", [$search]);
            })
            ->limit(10)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'users' => $users->map(function($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'username' => $u->username,
                    ];
                }),
            ]);
        }

        $user = auth()->user();
        if ($user) {
            $friends = $user->friends()->pluck('id')->toArray();
        } else {
            $friends = [];
        }

        $friendService = app(FriendService::class);
        return view("pages.search.index", ['users' => $users, "friends" => $friends, "friendService" => $friendService]);
    }
}
