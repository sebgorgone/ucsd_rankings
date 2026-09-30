<?php

namespace App;

enum MatchupStatus: string
{
    case Pending = 'pending';
    case Open = 'open';
    case Tied = 'tied';
    case Completed = 'completed';
    case Bye = 'bye';
}
