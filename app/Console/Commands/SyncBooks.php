<?php

namespace App\Console\Commands;

use App\Services\BookSyncService;
use Illuminate\Console\Command;

class SyncBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize books from External Admin API to Local Database';

    /**
     * Execute the console command.
     */
    public function handle(BookSyncService $service)
    {
        $this->info('Starting book synchronization...');
        
        $result = $service->syncBooks();
        
        if ($result['success']) {
            $this->info($result['message']);
        } else {
            $this->error($result['message']);
        }
    }
}
