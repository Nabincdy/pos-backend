<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\AccountHeadResource;
use App\Models\Account\AccountHead;

class AccountHeadController extends Controller
{
    public function __invoke()
    {
        return AccountHeadResource::collection(AccountHead::all());
    }
}
