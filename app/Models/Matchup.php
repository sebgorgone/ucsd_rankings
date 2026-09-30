<?php

namespace App\Models;

use App\MatchupStatus;
use Database\Factories\MatchupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matchup extends Model
{
    /** @use HasFactory<MatchupFactory> */
    use HasFactory;

    protected $fillable = [
        'round',
        'position',
        'sequence',
        'candidate_one_id',
        'candidate_two_id',
        'winner_candidate_id',
        'status',
        'opens_at',
        'closes_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => MatchupStatus::class,
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    public function bracket(): BelongsTo
    {
        return $this->belongsTo(Bracket::class);
    }

    public function candidateOne(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_one_id');
    }

    public function candidateTwo(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_two_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'winner_candidate_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function containsCandidate(int $candidateId): bool
    {
        return in_array($candidateId, [$this->candidate_one_id, $this->candidate_two_id], true);
    }
}
