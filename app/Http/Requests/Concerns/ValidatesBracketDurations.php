<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Validator;

trait ValidatesBracketDurations
{
    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny([
                    'candidate_phase_days',
                    'candidate_phase_hours',
                    'candidate_phase_minutes',
                    'matchup_days',
                    'matchup_hours',
                    'matchup_minutes',
                ])) {
                    return;
                }

                $candidatePhaseDuration = $this->durationInMinutes('candidate_phase');
                $matchupDuration = $this->durationInMinutes('matchup');

                if ($candidatePhaseDuration < 1 || $candidatePhaseDuration > 525600) {
                    $validator->errors()->add(
                        'candidate_phase_days',
                        'The candidate phase must be between 1 minute and 365 days.',
                    );
                }

                if ($matchupDuration < 1 || $matchupDuration > 10080) {
                    $validator->errors()->add(
                        'matchup_days',
                        'Each matchup must be between 1 minute and 7 days.',
                    );
                }

                if ($this->hasAny([
                    'extend_current_matchup_days',
                    'extend_current_matchup_hours',
                    'extend_current_matchup_minutes',
                ]) && $this->durationInMinutes('extend_current_matchup') > 10080) {
                    $validator->errors()->add(
                        'extend_current_matchup_days',
                        'The matchup extension may not exceed 7 days.',
                    );
                }
            },
        ];
    }

    public function durationInMinutes(string $prefix): int
    {
        return ($this->integer("{$prefix}_days") * 1440)
            + ($this->integer("{$prefix}_hours") * 60)
            + $this->integer("{$prefix}_minutes");
    }
}
