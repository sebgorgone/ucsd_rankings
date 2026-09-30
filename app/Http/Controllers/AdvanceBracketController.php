<?php

namespace App\Http\Controllers;

use App\Models\Bracket;
use App\Services\BracketProgressor;
use Illuminate\Http\RedirectResponse;

class AdvanceBracketController extends Controller
{
    public function __invoke(Bracket $bracket, BracketProgressor $progressor): RedirectResponse
    {
        $this->authorize('advance', $bracket);
        $progressor->advanceNow($bracket);

        return back()->with('status', 'The bracket advanced to the next stage.');
    }
}
