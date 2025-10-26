<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Compte;
use App\Models\Transaction;

class ArchiveExpiredBlockedAccounts implements ShouldQueue
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
            ->where('dateDebutBlocage', '<=', now())
            ->get();

        foreach ($expiredAccounts as $compte) {
            // Archive the account
            $compte->update(['statut' => 'ferme']);

            // Archive all transactions
            Transaction::where('compte_id', $compte->id)->update(['deleted_at' => now()]);
        }
    }
}
