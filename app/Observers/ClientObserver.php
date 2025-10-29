<?php

namespace App\Observers;

use App\Models\Client;

/**
 * Minimal Client observer to satisfy model registration during seeding.
 * Implement empty handlers to avoid side effects during automated seed runs.
 */
class ClientObserver
{
    public function creating(Client $client): void
    {
        // Intentionally left blank for seeding context.
    }

    public function created(Client $client): void
    {
        // Intentionally left blank for seeding context.
    }

    public function updating(Client $client): void
    {
        // Intentionally left blank for seeding context.
    }

    public function updated(Client $client): void
    {
        // Intentionally left blank for seeding context.
    }
}
