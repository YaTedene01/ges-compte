<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientAuthenticationMail extends Mailable
{
     use Queueable, SerializesModels;

     public $client;

     /**
      * Create a new message instance.
      */
     public function __construct(Client $client)
     {
         $this->client = $client;
     }

     /**
      * Get the message envelope.
      */
     public function envelope(): Envelope
     {
         return new Envelope(
             subject: 'Authentification de votre compte',
         );
     }

     /**
      * Get the message content definition.
      */
     public function content(): Content
     {
         return new Content(
             view: 'emails.client_authentication',
             with: [
                 'client' => $this->client,
             ],
         );
     }

     /**
      * Get the attachments for the message.
      *
      * @return array<int, \Illuminate\Mail\Mailables\Attachment>
      */
     public function attachments(): array
     {
         return [];
     }
}
