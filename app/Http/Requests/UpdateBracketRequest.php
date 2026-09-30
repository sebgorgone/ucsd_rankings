<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesBracketDurations;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBracketRequest extends FormRequest
{
    use ValidatesBracketDurations;

    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('bracket')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'candidate_phase_days' => ['required', 'integer', 'min:0', 'max:365'],
            'candidate_phase_hours' => ['required', 'integer', 'min:0', 'max:23'],
            'candidate_phase_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'matchup_days' => ['required', 'integer', 'min:0', 'max:7'],
            'matchup_hours' => ['required', 'integer', 'min:0', 'max:23'],
            'matchup_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'allow_candidate_submissions' => ['sometimes', 'boolean'],
            'extend_current_matchup_days' => ['nullable', 'integer', 'min:0', 'max:7'],
            'extend_current_matchup_hours' => ['nullable', 'integer', 'min:0', 'max:23'],
            'extend_current_matchup_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
        ];
    }
}
