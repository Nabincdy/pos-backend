<?php

namespace App\Observers;

use App\Models\Hr\Employee;

class EmployeeObserver
{
    public function creating(Employee $employee)
    {
        if (is_null($employee->rank)) {
            $employee->rank = Employee::max('rank') + 1;

            return;
        }

        $lowerPriorityEmployees = Employee::where('rank', '>=', $employee->rank)
            ->get();

        foreach ($lowerPriorityEmployees as $lowerPriorityEmployee) {
            $lowerPriorityEmployee->rank++;
            $lowerPriorityEmployee->saveQuietly();
        }
    }

    public function updating(Employee $employee)
    {
        if ($employee->isClean('rank')) {
            return;
        }

        if (is_null($employee->rank)) {
            $employee->rank = Employee::max('rank');
        }

        if ($employee->getOriginal('rank') > $employee->rank) {
            $rankRange = [
                $employee->rank, $employee->getOriginal('rank'),
            ];
        } else {
            $rankRange = [
                $employee->getOriginal('rank'), $employee->rank,
            ];
        }

        $lowerPriorityEmployees = Employee::whereBetween('rank', $rankRange)
            ->where('id', '!=', $employee->id)
            ->get();

        foreach ($lowerPriorityEmployees as $lowerPriorityEmployee) {
            if ($employee->getOriginal('rank') < $employee->rank) {
                $lowerPriorityEmployee->rank--;
            } else {
                $lowerPriorityEmployee->rank++;
            }
            $lowerPriorityEmployee->saveQuietly();
        }
    }

    public function deleting(Employee $employee)
    {
        $lowerPriorityEmployees = Employee::where('rank', '>', $employee->rank)
            ->get();

        foreach ($lowerPriorityEmployees as $lowerPriorityEmployee) {
            $lowerPriorityEmployee->rank--;
            $lowerPriorityEmployee->saveQuietly();
        }
    }
}
