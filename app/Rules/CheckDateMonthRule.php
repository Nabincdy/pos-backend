<?php

namespace App\Rules;

use App\Models\Setting\Month;
use Illuminate\Contracts\Validation\Rule;

class CheckDateMonthRule implements Rule
{
    protected Month $month;

    public function __construct($month_id)
    {
        $this->month = Month::find($month_id);
    }

    public function passes($attribute, $value): bool
    {
        $dateArray = explode('-', $value);

        return $dateArray[1] == $this->month->month;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute not matched with month';
    }
}
