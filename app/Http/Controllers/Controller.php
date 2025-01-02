<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
 public const PER_PAGE               = 50;
 public const DEFAULT_SORT_FIELD     = 'created_at';
 public const DEFAULT_SORT_DIRECTION = 'desc';
 use AuthorizesRequests, ValidatesRequests, DispatchesJobs;
}
