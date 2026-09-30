<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCandidateRequest;
use App\Models\Bracket;
use App\Services\BracketProgressor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CandidateController extends Controller
{
    public function __invoke(
        StoreCandidateRequest $request,
        Bracket $bracket,
        BracketProgressor $progressor,
    ): RedirectResponse {
        $progressor->progress($bracket);
        $bracket->refresh();

        if (! $bracket->isAcceptingCandidates()) {
            throw ValidationException::withMessages([
                'name' => 'This bracket is no longer accepting candidates.',
            ]);
        }

        $bracket->candidates()->create([
            'name' => $request->validated('name'),
            'submitted_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Candidate added.');
    }
}
