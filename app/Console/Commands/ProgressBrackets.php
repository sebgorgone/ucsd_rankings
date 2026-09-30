<?php

namespace App\Console\Commands;

use App\Services\BracketProgressor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('brackets:progress')]
#[Description('Advance bracket phases and close expired matchups')]
class ProgressBrackets extends Command
{
    public function handle(BracketProgressor $progressor): int
    {
        $count = $progressor->progressDue();

        $this->info("Processed {$count} due bracket(s).");

        return self::SUCCESS;
    }
}
