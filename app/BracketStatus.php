<?php

namespace App;

enum BracketStatus: string
{
    case Candidate = 'candidate';
    case Ongoing = 'ongoing';
    case Tied = 'tied';
    case Completed = 'completed';
}
