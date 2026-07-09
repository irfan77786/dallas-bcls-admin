<?php

namespace App\Console\Commands;

use App\Models\Airport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedUsaAirports extends Command
{
    protected $signature = 'airports:seed-usa
                            {--fresh : Remove existing airport records before seeding}
                            {--no-download : Only use bundled airport data (skip CSV download)}';

    protected $description = 'Seed USA airports into the airports table (includes all Dallas-area airports)';

    public function handle(): int
    {
        if (! Schema::hasTable('airports')) {
            $this->error('The airports table does not exist. Run migrations first.');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('airports', 'state')) {
            $this->warn('Run migrations to add the state column on airports.');
        }

        $airports = $this->resolveAirportRows();
        if ($airports === []) {
            $this->error('No airport data found to seed.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            DB::table('airports')->truncate();
            $this->warn('Existing airport records removed.');
        }

        $now = now();
        $bar = $this->output->createProgressBar(count($airports));
        $bar->start();

        $count = 0;
        foreach ($airports as $row) {
            Airport::updateOrCreate(
                ['iata_code' => $row['iata_code']],
                [
                    'name' => $row['name'],
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Seeded {$count} USA airports.");

        $dallasCount = collect($airports)->filter(function ($row) {
            $city = strtolower((string) ($row['city'] ?? ''));
            $name = strtolower((string) ($row['name'] ?? ''));

            return str_contains($city, 'dallas')
                || str_contains($name, 'dallas')
                || in_array($row['iata_code'], ['DAL', 'DFW', 'ADS', 'RBD', 'AFW', 'FTW', 'GKY', 'TKI', 'GPM', 'RVO', 'LNC', 'FWS'], true);
        })->count();

        $this->line("Dallas-area airports included: {$dallasCount}");

        return self::SUCCESS;
    }

    /**
     * @return list<array{iata_code: string, name: string, city: ?string, state: ?string}>
     */
    private function resolveAirportRows(): array
    {
        $csvRows = $this->option('no-download') ? [] : $this->loadFromOurAirportsCsv();
        $bundledRows = require database_path('data/usa_airports.php');

        if ($csvRows !== []) {
            $this->info('Loaded '.count($csvRows).' airports from OurAirports CSV.');

            return $this->dedupeByIataCode(array_merge($csvRows, $bundledRows));
        }

        $this->warn('Using bundled airport data only. Re-run without --no-download for full USA list.');

        return $this->dedupeByIataCode($bundledRows);
    }

    /**
     * @return list<array{iata_code: string, name: string, city: ?string, state: ?string}>
     */
    private function loadFromOurAirportsCsv(): array
    {
        $path = database_path('data/airports.csv');
        if (! is_file($path)) {
            $this->info('Downloading OurAirports dataset...');
            $context = stream_context_create([
                'http' => ['timeout' => 120],
                'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);
            $contents = @file_get_contents(
                'https://davidmegginson.github.io/ourairports-data/airports.csv',
                false,
                $context
            );

            if ($contents === false || $contents === '') {
                $this->warn('Could not download OurAirports CSV.');

                return [];
            }

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, $contents);
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);
        if (! is_array($header)) {
            fclose($handle);

            return [];
        }

        $indexes = array_flip($header);
        $required = ['iso_country', 'iata_code', 'name', 'municipality', 'iso_region'];
        foreach ($required as $column) {
            if (! array_key_exists($column, $indexes)) {
                fclose($handle);
                $this->warn('Unexpected OurAirports CSV format.');

                return [];
            }
        }

        $rows = [];
        while (($line = fgetcsv($handle)) !== false) {
            if (! isset($line[$indexes['iso_country']]) || $line[$indexes['iso_country']] !== 'US') {
                continue;
            }

            $iata = strtoupper(trim((string) ($line[$indexes['iata_code']] ?? '')));
            if ($iata === '') {
                continue;
            }

            $region = strtoupper(trim((string) ($line[$indexes['iso_region']] ?? '')));
            $state = str_starts_with($region, 'US-') ? substr($region, 3) : null;

            $rows[] = [
                'iata_code' => $iata,
                'name' => trim((string) ($line[$indexes['name']] ?? '')),
                'city' => trim((string) ($line[$indexes['municipality']] ?? '')) ?: null,
                'state' => $state,
            ];
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @param  list<array{iata_code: string, name: string, city: ?string, state: ?string}>  $rows
     * @return list<array{iata_code: string, name: string, city: ?string, state: ?string}>
     */
    private function dedupeByIataCode(array $rows): array
    {
        $unique = [];
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) ($row['iata_code'] ?? '')));
            if ($code === '') {
                continue;
            }

            $row['iata_code'] = $code;
            $unique[$code] = $row;
        }

        uasort($unique, fn ($a, $b) => strcmp($a['iata_code'], $b['iata_code']));

        return array_values($unique);
    }
}
