<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedHistoricalData extends Command
{
    protected $signature = 'db:seed-historical 
                            {--january : Seed January 2026}
                            {--february : Seed February 2026}
                            {--march : Seed March 2026}
                            {--days= : Number of days to seed for March}
                            {--all : Seed all historical data (Jan, Feb, Mar)}';

    protected $description = 'Seed database with historical test data';

    public function handle(): int
    {
        $seeder = new \Database\Seeders\DatabaseSeeder();
        $seeder->setCommand($this);

        $seedAll = $this->option('all');
        $seedJan = $this->option('january');
        $seedFeb = $this->option('february');
        $seedMar = $this->option('march');
        $days = $this->option('days');

        if ($seedAll) {
            $this->info('Seeding all historical data (January, February, March 2026)...');
            $seeder->seedJanuary2026();
            $seeder->seedFebruary2026();
            $seeder->seedMarch2026();
        } elseif ($seedJan || $seedFeb || $seedMar) {
            if ($seedJan) {
                $this->info('Seeding January 2026...');
                $seeder->seedJanuary2026();
            }
            if ($seedFeb) {
                $this->info('Seeding February 2026...');
                $seeder->seedFebruary2026();
            }
            if ($seedMar) {
                $this->info('Seeding March 2026...' . ($days ? " (first $days days)" : ""));
                $seeder->seedMarch2026($days ? (int)$days : null);
            }
        } else {
            $this->info('Seeding March 2026 (first 5 days)...');
            $seeder->seedMarch2026(5);
        }

        $totalTickets = \App\Models\Ticket::count();
        $totalPayments = \App\Models\Payment::count();
        $this->info("Total: $totalTickets tickets, $totalPayments payments");

        return Command::SUCCESS;
    }
}
