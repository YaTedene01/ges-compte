<?php

namespace App\Listeners;

use App\Events\ClientCreated;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientAuthenticationMail;
use Illuminate\Support\Facades\Log;

class SendClientNotification
{
     /**
      * Handle the event.
      */
     public function handle(ClientCreated $event): void
     {
         $client = $event->client;

         // Send email
         try {
             Mail::to($client->email)->send(new ClientAuthenticationMail($client));
             Log::info('Email sent to client: ' . $client->email);
         } catch (\Exception $e) {
             Log::error('Failed to send email to client: ' . $client->email . ' - ' . $e->getMessage());
         }

         // Send SMS (assuming an SMS service is configured)
         try {
             // Example: SMS::send($client->telephone, 'Votre code d\'authentification est: ' . $client->code);
             Log::info('SMS sent to client: ' . $client->telephone . ' with code: ' . $client->code);
         } catch (\Exception $e) {
             Log::error('Failed to send SMS to client: ' . $client->telephone . ' - ' . $e->getMessage());
         }
     }
}
