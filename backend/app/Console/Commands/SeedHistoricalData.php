<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedHistoricalData extends Command
{
    protected $signature = 'db:seed-historical 
                            {--february : Seed February 2026}
                            {--march : Seed March 2026}
                            {--june : Seed June 2026}
                            {--all : Seed all historical data (Feb, Mar, Jun)}';

    protected $description = 'Seed database with historical test data';

    public function handle(): int
    {
        $seeder = new \Database\Seeders\DatabaseSeeder();
        $seeder->setCommand($this);

        $seedAll = $this->option('all');
        $seedFeb = $this->option('february');
        $seedMar = $this->option('march');
        $seedJun = $this->option('june');

        if ($seedAll || (!$seedFeb && !$seedMar && !$seedJun)) {
            $this->info('Seeding all historical data (February, March, June 2026)...');
            $seeder->seedFebruary2026();
            $seeder->seedMarch2026();
            $seeder->seedJune2026();
        } else {
            if ($seedFeb) {
                $seeder->seedFebruary2026();
            }
            if ($seedMar) {
                $seeder->seedMarch2026();
            }
            if ($seedJun) {
                $seeder->seedJune2026();
            }
        }

        $totalTickets = \App\Models\Ticket::count();
        $totalPayments = \App\Models\Payment::count();
        $this->info("Total: $totalTickets tickets, $totalPayments payments");

        return Command::SUCCESS;
    }
}
