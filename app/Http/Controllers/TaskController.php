<?php

namespace App\Http\Controllers;

use App\Traits\MessageTrait;
use Illuminate\Http\Request;

class TaskController extends Controller
{
  use \App\Traits\MessageTrait;

  public function scheduledTasks()
  {
    $data = [
      'message_count' => $this->getCount()
    ];

    return response()->json($data);
  }
}
