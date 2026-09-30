<?php

namespace App\Http\Controllers;

use App\Models\Bracket;
use Illuminate\Contracts\View\View;

class BracketStatsController extends Controller
{
    public function __invoke(Bracket $bracket): View
    {
        $this->authorize('viewStats', $bracket);

        $bracket->load([
            'user',
            'matchups' => fn ($query) => $query
                ->with([
                    'candidateOne',
                    'candidateTwo',
                    'winner',
                    'votes' => fn ($votes) => $votes
                        ->with(['user:id,name,email', 'candidate:id,name'])
                        ->latest(),
                ])
                ->orderBy('sequence'),
        ]);

        return view('brackets.stats', compact('bracket'));
    }
}
