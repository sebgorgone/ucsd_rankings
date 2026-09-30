@php
    $editing = isset($bracket);
    $candidatePhaseLocked = $editing && $bracket->status !== \App\BracketStatus::Candidate;
    $candidatePhaseTotal = $bracket->candidate_phase_duration_minutes ?? 1440;
    $matchupTotal = $bracket->matchup_duration_minutes ?? 60;

    $candidatePhaseDays = intdiv($candidatePhaseTotal, 1440);
    $candidatePhaseHours = intdiv($candidatePhaseTotal % 1440, 60);
    $candidatePhaseMinutes = $candidatePhaseTotal % 60;
    $matchupDays = intdiv($matchupTotal, 1440);
    $matchupHours = intdiv($matchupTotal % 1440, 60);
    $matchupMinutes = $matchupTotal % 60;
@endphp

<div class="grid gap-6">
    <div>
        <x-input-label for="name" value="Bracket name" />
        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full"
            :value="old('name', $bracket->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <fieldset>
            <legend class="text-sm font-semibold text-slate-700">Candidate phase duration</legend>
            <div class="mt-2 grid grid-cols-3 gap-2">
                <div>
                    <x-input-label for="candidate_phase_days" value="Days" class="text-xs" />
                    <x-text-input id="candidate_phase_days" name="candidate_phase_days" type="number"
                        min="0" max="365" class="mt-1 block w-full"
                        :value="old('candidate_phase_days', $candidatePhaseDays)"
                        :disabled="$candidatePhaseLocked" required />
                </div>
                <div>
                    <x-input-label for="candidate_phase_hours" value="Hours" class="text-xs" />
                    <x-text-input id="candidate_phase_hours" name="candidate_phase_hours" type="number"
                        min="0" max="23" class="mt-1 block w-full"
                        :value="old('candidate_phase_hours', $candidatePhaseHours)"
                        :disabled="$candidatePhaseLocked" required />
                </div>
                <div>
                    <x-input-label for="candidate_phase_minutes" value="Minutes" class="text-xs" />
                    <x-text-input id="candidate_phase_minutes" name="candidate_phase_minutes" type="number"
                        min="0" max="59" class="mt-1 block w-full"
                        :value="old('candidate_phase_minutes', $candidatePhaseMinutes)"
                        :disabled="$candidatePhaseLocked" required />
                </div>
            </div>

            @if ($candidatePhaseLocked)
                <input type="hidden" name="candidate_phase_days" value="{{ $candidatePhaseDays }}">
                <input type="hidden" name="candidate_phase_hours" value="{{ $candidatePhaseHours }}">
                <input type="hidden" name="candidate_phase_minutes" value="{{ $candidatePhaseMinutes }}">
            @endif

            <x-input-error :messages="$errors->get('candidate_phase_days')" class="mt-2" />
            <x-input-error :messages="$errors->get('candidate_phase_hours')" class="mt-2" />
            <x-input-error :messages="$errors->get('candidate_phase_minutes')" class="mt-2" />
            <p class="mt-2 text-xs text-slate-500">Starts when the bracket is created and may only be extended.</p>
        </fieldset>

        <fieldset>
            <legend class="text-sm font-semibold text-slate-700">Each matchup duration</legend>
            <div class="mt-2 grid grid-cols-3 gap-2">
                <div>
                    <x-input-label for="matchup_days" value="Days" class="text-xs" />
                    <x-text-input id="matchup_days" name="matchup_days" type="number"
                        min="0" max="7" class="mt-1 block w-full"
                        :value="old('matchup_days', $matchupDays)" required />
                </div>
                <div>
                    <x-input-label for="matchup_hours" value="Hours" class="text-xs" />
                    <x-text-input id="matchup_hours" name="matchup_hours" type="number"
                        min="0" max="23" class="mt-1 block w-full"
                        :value="old('matchup_hours', $matchupHours)" required />
                </div>
                <div>
                    <x-input-label for="matchup_minutes" value="Minutes" class="text-xs" />
                    <x-text-input id="matchup_minutes" name="matchup_minutes" type="number"
                        min="0" max="59" class="mt-1 block w-full"
                        :value="old('matchup_minutes', $matchupMinutes)" required />
                </div>
            </div>
            <x-input-error :messages="$errors->get('matchup_days')" class="mt-2" />
            <x-input-error :messages="$errors->get('matchup_hours')" class="mt-2" />
            <x-input-error :messages="$errors->get('matchup_minutes')" class="mt-2" />
        </fieldset>
    </div>

    @if (! $editing || $bracket->status === \App\BracketStatus::Candidate)
        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <input type="hidden" name="allow_candidate_submissions" value="0">
            <input type="checkbox" name="allow_candidate_submissions" value="1"
                class="mt-1 rounded border-slate-300 text-slate-800 focus:ring-slate-500"
                @checked(old('allow_candidate_submissions', $bracket->allow_candidate_submissions ?? false))>
            <span>
                <span class="block font-semibold text-slate-900">Allow community candidates</span>
                <span class="block text-sm text-slate-600">Authenticated users may add candidates until the candidate deadline.</span>
            </span>
        </label>
    @endif

    @if ($editing && $bracket->status === \App\BracketStatus::Ongoing)
        <fieldset>
            <legend class="text-sm font-semibold text-slate-700">Extend current matchup</legend>
            <div class="mt-2 grid max-w-md grid-cols-3 gap-2">
                <div>
                    <x-input-label for="extend_current_matchup_days" value="Days" class="text-xs" />
                    <x-text-input id="extend_current_matchup_days" name="extend_current_matchup_days" type="number"
                        min="0" max="7" class="mt-1 block w-full" :value="old('extend_current_matchup_days', 0)" />
                </div>
                <div>
                    <x-input-label for="extend_current_matchup_hours" value="Hours" class="text-xs" />
                    <x-text-input id="extend_current_matchup_hours" name="extend_current_matchup_hours" type="number"
                        min="0" max="23" class="mt-1 block w-full" :value="old('extend_current_matchup_hours', 0)" />
                </div>
                <div>
                    <x-input-label for="extend_current_matchup_minutes" value="Minutes" class="text-xs" />
                    <x-text-input id="extend_current_matchup_minutes" name="extend_current_matchup_minutes" type="number"
                        min="0" max="59" class="mt-1 block w-full" :value="old('extend_current_matchup_minutes', 0)" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('extend_current_matchup_days')" class="mt-2" />
            <x-input-error :messages="$errors->get('extend_current_matchup_hours')" class="mt-2" />
            <x-input-error :messages="$errors->get('extend_current_matchup_minutes')" class="mt-2" />
        </fieldset>
    @endif
</div>
