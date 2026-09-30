<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bracket;
use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->withCount(['brackets', 'votes'])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load([
            'brackets' => fn ($query) => $query
                ->with(['winner'])
                ->withCount('candidates')
                ->latest(),
        ])->loadCount('votes');

        $votedBrackets = Bracket::query()
            ->whereHas('matchups.votes', fn ($query) => $query->where('user_id', $user->id))
            ->with(['winner'])
            ->withCount([
                'candidates',
                'matchups as voted_matchups_count' => fn ($query) => $query
                    ->whereHas('votes', fn ($votes) => $votes->where('user_id', $user->id)),
            ])
            ->latest()
            ->get();

        return view('admin.users.show', compact('user', 'votedBrackets'));
    }
}
