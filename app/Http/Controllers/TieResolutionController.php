<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolveTieRequest;
use App\Models\Candidate;
use App\Models\Matchup;
use App\Services\BracketProgressor;
use Illuminate\Http\RedirectResponse;

class TieResolutionController extends Controller
{
    public function __invoke(
        ResolveTieRequest $request,
        Matchup $matchup,
        BracketProgressor $progressor,
    ): RedirectResponse {
        $winner = Candidate::query()->findOrFail($request->integer('candidate_id'));
        $progressor->resolveTie($matchup, $winner);

        return back()->with('status', 'Tie resolved and the bracket advanced.');
    }
}
