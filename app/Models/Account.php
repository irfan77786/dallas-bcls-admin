<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    protected $fillable = [
        'company_number',
        'company_name',
        'email',
        'address',
        'phone',
    ];

    public function billingContact(): HasOne
    {
        return $this->hasOne(BillingContact::class);
    }

    public function addressPreview(int $maxChars = 20): string
    {
        $t = (string) $this->address;
        if (mb_strlen($t) > $maxChars) {
            return mb_substr($t, 0, $maxChars) . '...';
        }

        return $t;
    }

    /**
     * Random US EIN-style identifier (##-#######), unique in `accounts.company_number`.
     * Not derived from the row id, so it does not look sequential.
     */
    public static function generateUniqueCompanyNumber(): string
    {
        for ($attempt = 0; $attempt < 64; $attempt++) {
            $a = str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
            $b = str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
            $value = $a . '-' . $b;
            if (! static::query()->where('company_number', $value)->exists()) {
                return $value;
            }
        }

        // Extremely unlikely: fall back to alphanumeric code (max 20 chars in schema)
        do {
            $value = 'US-' . strtoupper(bin2hex(random_bytes(4)));
        } while (static::query()->where('company_number', $value)->exists());

        return $value;
    }

    /** @param  string|null  $digits  10-digit US number */
    public static function formatUsPhone(?string $digits): ?string
    {
        if ($digits === null || $digits === '') {
            return null;
        }
        $d = preg_replace('/\D/', '', $digits);
        if (strlen($d) < 10) {
            return $digits;
        }
        if (strlen($d) > 10 && str_starts_with($d, '1')) {
            $d = substr($d, -10);
        }
        if (strlen($d) < 10) {
            return $digits;
        }

        return sprintf('(%s) %s-%s', substr($d, 0, 3), substr($d, 3, 3), substr($d, 6, 4));
    }

    public static function normalizeUsPhoneToDigits(string $value): string
    {
        $d = preg_replace('/\D/', '', $value);
        if (strlen($d) === 11 && str_starts_with($d, '1')) {
            $d = substr($d, 1);
        }

        return $d;
    }
}
