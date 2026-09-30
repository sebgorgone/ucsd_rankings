<?php

namespace App\Http\Controllers;

use App\Models\Bracket;
use App\Services\BracketProgressor;
use Illuminate\Http\RedirectResponse;

class ToggleBracketPauseController extends Controller
{
    public function __invoke(Bracket $bracket, BracketProgressor $progressor): RedirectResponse
    {
        $this->authorize('pause', $bracket);
        $progressor->progress($bracket);
        $paused = $progressor->togglePause($bracket->fresh());

        return back()->with('status', $paused ? 'The phase timer is paused.' : 'The phase timer has resumed.');
    }
}
