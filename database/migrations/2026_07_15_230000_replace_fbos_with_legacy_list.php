<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fbos')) {
            return;
        }

        Schema::table('fbos', function (Blueprint $table) {
            if (! Schema::hasColumn('fbos', 'phone')) {
                $table->string('phone', 64)->nullable()->after('zip');
            }
            if (! Schema::hasColumn('fbos', 'notes')) {
                $table->string('notes', 255)->nullable()->after('phone');
            }
        });

        DB::table('fbos')->delete();

        $now = now();
        $rows = [];
        $sort = 10;

        foreach ($this->legacyFbos() as $row) {
            $street1 = trim((string) ($row['street1'] ?? ''));
            $street2 = trim((string) ($row['street2'] ?? ''));
            if ($street1 === '' && $street2 !== '') {
                $street1 = $street2;
                $street2 = '';
            }

            $state = trim((string) ($row['state'] ?? ''));
            if (strcasecmp($state, 'TX') === 0) {
                $state = 'Texas';
            }

            $rows[] = [
                'name' => $row['name'],
                'airport_code' => $row['code'] !== '' ? $row['code'] : null,
                'address_line1' => $street1 !== '' ? $street1 : null,
                'address_line2' => $street2 !== '' ? $street2 : null,
                'city' => ($row['city'] ?? '') !== '' ? $row['city'] : null,
                'state' => $state !== '' ? $state : null,
                'zip' => ($row['zip'] ?? '') !== '' ? $row['zip'] : null,
                'phone' => ($row['phone'] ?? '') !== '' ? $row['phone'] : null,
                'notes' => ($row['notes'] ?? '') !== '' ? $row['notes'] : null,
                'country' => 'United States',
                'is_active' => true,
                'sort_order' => $sort,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $sort += 10;
        }

        DB::table('fbos')->insert($rows);
    }

    public function down(): void
    {
        if (! Schema::hasTable('fbos')) {
            return;
        }

        Schema::table('fbos', function (Blueprint $table) {
            if (Schema::hasColumn('fbos', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('fbos', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }

    /**
     * Pipe format from legacy tripFBOInfo select:
     * code|name|street1|street2|city|state|zip|phone|notes
     *
     * @return list<array{code:string,name:string,street1:string,street2:string,city:string,state:string,zip:string,phone:string,notes:string}>
     */
    private function legacyFbos(): array
    {
        $raw = [
            'ADI|Alliance Airport|2221 Alliance Blvd||Fort Worth|TX|76177||',
            'ADI|Ambassador Aviation|5435 SATURN DR||DALLAS||||',
            'ADI|American Aero|251 AMERICAN CONCOURSE||FORT WORTH||76106|817-289-8000|',
            'ADI|Atlantic Aviation ADS|4400 Glenn Curtiss Dr,||Addison|TX|75001||',
            'ADI|Atlantic Aviation DAL|3232 LOVEFIELD DR||DALLAS|TX|75235|+12146540994|',
            'ADI|Business Jet Center|8611 Lemmon Drive||Dallas|TX|75209||',
            'DFW|CORPORATE AVIATION FBO|1816 North 24th Av||DFW AIRPORT|||+19725743390|',
            'ADI|EM DAL HANGAR|3250 Love Field Drive||Dallas|TX|||',
            'ADI|FlexJet DAL|7701 LEMMON AVE||DALLAS|TX|75209|+18664730025|',
            'ADI|Fort Worth Alliance|2221 Alliance Blvd||Fort Worth|TX|76177||',
            'ADI|FORT WORTH MEECHAM|201 American Concourse||Fort Worth|TX|||',
            'ADI|Galaxy FBO - ADS - Addison Airport||15625 Addison Rd,|Addison|TX|75001||',
            'ADI|Harrison Aviation|5070 S. Collins St||Arlington|TX|76004|+18175570350|ARLINGTON MUNICIPAL (GKY)',
            'ADI|JET AVIATION|7363 Herb Kelleher Way||DALLAS|TX|75235||',
            'ADI|JSX Dallas|7201 LEMMON Ave||DALLAS|TX|75235|8004359579|',
            'ADI|Million Air|4300 WESTRGROVE||ADDISON|TX|75001||',
            'ADI|Modern Aviation FTW|251 American Concourse||Fort Worth|TX|76106|+18172898000|',
            'ADI|Signature Aviation DAL - Dallas Love Field Terminal 1|8001 LEMMON AVE||DALLAS||75209|+12149561000|',
            'ADI|Signature Aviation DAL - Terminal 2|7515 Lemmon Ave||Dallas|TX|75209|+12143537000|',
            'ADI|Signature Aviation DAL Terminal 4 - Dallas Love Field|7701 Lemmon Ave|100|Dallas|TX|75209|+12142147701|',
            'ADI|Signature Flight Support Terminal: Term 3|8321 lEMMON AVE||DALLAS|TX|75209|+12143511872|Terminal: Term 3',
            '|TAC Air - DAL|7701 Lemmon Ave|100|Dallas|TX|75209|+12142147701|',
            'ADI|Texas Jet|200 Texas Way||Fort Worth|TX|76106|+18176248438|',
            'ADI|TEXTAR AVIATION|3232 LOVE FIELD DR||DALLAS|TX|75235||',
        ];

        $out = [];
        foreach ($raw as $line) {
            $parts = array_pad(explode('|', $line), 9, '');
            $out[] = [
                'code' => trim($parts[0]),
                'name' => trim($parts[1]),
                'street1' => trim($parts[2]),
                'street2' => trim($parts[3]),
                'city' => trim($parts[4]),
                'state' => trim($parts[5]),
                'zip' => trim($parts[6]),
                'phone' => trim($parts[7]),
                'notes' => trim($parts[8]),
            ];
        }

        return $out;
    }
};
