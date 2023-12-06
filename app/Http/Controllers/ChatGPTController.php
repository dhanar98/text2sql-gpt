<?php

namespace App\Http\Controllers;

use App\Traits\DatabaseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatGPTController extends Controller
{
    use DatabaseTrait;


    public function welcome()
    {
        try {
            $fetchedDbTableNames = $this->getAllTableNames();
            if(count($fetchedDbTableNames) === 0){
                return view('error');
            }
            return view('welcome', ['fetchedDbTableNames' => $fetchedDbTableNames]);
        } catch (\Exception $e) {
            Log::error("Welcome Page ====> {$e->getMessage()}");
        }
    }
}
