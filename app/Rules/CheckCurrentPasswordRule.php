<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class CheckCurrentPasswordRule implements Rule
{
    public function __construct()
    {
        //
    }

    public function passes($attribute, $value): bool
    {
        return Hash::check($value, auth()->user()->password ?? '');
    }

    public function message()
    {
        return 'The :attribute not matched with old password.';
    }
}
