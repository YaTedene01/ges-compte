<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientCreated
{
     use Dispatchable, SerializesModels;

     public $client;

     /**
      * Create a new event instance.
      */
     public function __construct(Client $client)
     {
         $this->client = $client;
     }
}
