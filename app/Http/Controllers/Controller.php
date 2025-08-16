<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function deleteFile($url): void
    {
        if (Storage::disk('public')->exists($url)) {
            Storage::disk('public')->delete($url);
        }
    }

    public function checkAuthorization($permission): void
    {
        abort_if(
            Gate::denies($permission),
            403,
            'You are not allowed to access this resource'
        );
    }
}
