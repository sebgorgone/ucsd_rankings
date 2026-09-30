<?php

namespace App\Models;

use App\BracketStatus;
use Database\Factories\BracketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bracket extends Model
{
    /** @use HasFactory<BracketFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'allow_candidate_submissions',
        'candidate_phase_duration_minutes',
        'candidate_phase_ends_at',
        'matchup_duration_minutes',
        'status',
        'bracket_size',
        'total_rounds',
        'started_at',
        'completed_at',
        'paused_at',
        'winner_candidate_id',
    ];

    protected function casts(): array
    {
        return [
            'allow_candidate_submissions' => 'boolean',
            'candidate_phase_ends_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'paused_at' => 'datetime',
            'status' => BracketStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function matchups(): HasMany
    {
        return $this->hasMany(Matchup::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'winner_candidate_id');
    }

    public function isAcceptingCandidates(): bool
    {
        return $this->status === BracketStatus::Candidate
            && ($this->paused_at !== null || $this->candidate_phase_ends_at->isFuture());
    }
}
