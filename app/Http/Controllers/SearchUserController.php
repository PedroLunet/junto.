<?php

namespace App\Http\Controllers;

use App\Models\User;

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
                $query
                    ->whereRaw("fts_document @@ plainto_tsquery('english', ?)", [$search])
                    ->orderByRaw("ts_rank(fts_document, plainto_tsquery('english', ?)) DESC", [$search]);
            })
            ->get();
        
        $friends = auth()->user()->friends()->pluck('id')->toArray();
        $friendService = app(FriendService::class);
        return view("pages.search.index", ['users' => $users,"friends" => $friends,"friendService" => $friendService]);
    }
}
