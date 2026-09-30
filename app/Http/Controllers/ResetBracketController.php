<?php

namespace App\Http\Controllers;

use App\Models\Bracket;
use App\Services\BracketProgressor;
use Illuminate\Http\RedirectResponse;

class ResetBracketController extends Controller
{
    public function __invoke(Bracket $bracket, BracketProgressor $progressor): RedirectResponse
    {
        $this->authorize('reset', $bracket);
        $progressor->resetTournament($bracket);

        return back()->with('status', 'The bracket was reset to matchup one and all votes were removed.');
    }
}
