<?php

namespace App\Observers;

use App\Models\Setting\Month;

class MonthObserver
{
    public function creating(Month $program)
    {
        if (is_null($program->rank)) {
            $program->rank = Month::max('rank') + 1;

            return;
        }

        $lowerPriorityMonths = Month::where('rank', '>=', $program->rank)
            ->get();

        foreach ($lowerPriorityMonths as $lowerPriorityMonth) {
            $lowerPriorityMonth->rank++;
            $lowerPriorityMonth->saveQuietly();
        }
    }

    public function updating(Month $program)
    {
        if ($program->isClean('rank')) {
            return;
        }

        if (is_null($program->rank)) {
            $program->rank = Month::max('rank');
        }

        if ($program->getOriginal('rank') > $program->rank) {
            $rankRange = [
                $program->rank, $program->getOriginal('rank'),
            ];
        } else {
            $rankRange = [
                $program->getOriginal('rank'), $program->rank,
            ];
        }

        $lowerPriorityMonths = Month::whereBetween('rank', $rankRange)
            ->where('id', '!=', $program->id)
            ->get();

        foreach ($lowerPriorityMonths as $lowerPriorityMonth) {
            if ($program->getOriginal('rank') < $program->rank) {
                $lowerPriorityMonth->rank--;
            } else {
                $lowerPriorityMonth->rank++;
            }
            $lowerPriorityMonth->saveQuietly();
        }
    }

    public function deleting(Month $program)
    {
        $lowerPriorityMonths = Month::where('rank', '>', $program->rank)
            ->get();

        foreach ($lowerPriorityMonths as $lowerPriorityMonth) {
            $lowerPriorityMonth->rank--;
            $lowerPriorityMonth->saveQuietly();
        }
    }
}
