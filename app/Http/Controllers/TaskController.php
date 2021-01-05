<?php

namespace App\Http\Controllers;

use App\Traits\MessageTrait;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class TaskController extends Controller
{
  use \App\Traits\MessageTrait;

  public function scheduledTasks()
  {
    return RB::success(['message_count' => $this->getCount()]);
  }
}
