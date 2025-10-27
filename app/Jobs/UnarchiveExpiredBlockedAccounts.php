<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Compte;

class UnarchiveExpiredBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expiredAccounts = Compte::where('statut', 'bloque')
            ->where('dateFinBlocage', '<=', now())
            ->get();

        foreach ($expiredAccounts as $compte) {
            // Unarchive the account
            $compte->update([
                'statut' => 'actif',
                'motifBlocage' => null,
                'dateDebutBlocage' => null,
                'dateFinBlocage' => null,
            ]);
        }
    }
}
