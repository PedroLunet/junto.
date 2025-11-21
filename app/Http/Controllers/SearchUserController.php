<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;

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

        return view("pages.search.index", ['users' => $users]);
    }
}
