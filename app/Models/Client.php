<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'phone', 'email'])]
class Client extends Model
{
    use HasUuid;

    /**
     * @return HasMany<Job, $this>
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Normalises Australian phone numbers to a consistent display format
     * (mobile: `04XX XXX XXX`, landline: `0X XXXX XXXX`) regardless of how
     * the digits were entered (spaces, dashes, +61 prefix, etc). Numbers
     * that don't match a recognisable 10-digit AU shape are left as typed.
     */
    /**
     * @return Attribute<string|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? static::formatAustralianPhoneNumber($value) : null,
        );
    }

    public static function formatAustralianPhoneNumber(string $value): string
    {
        $value = trim($value);
        $digits = preg_replace('/\D+/', '', $value);

        if (str_starts_with($digits, '61') && strlen($digits) === 11) {
            $digits = '0'.substr($digits, 2);
        }

        if (strlen($digits) !== 10 || $digits[0] !== '0') {
            return $value;
        }

        if ($digits[1] === '4') {
            return sprintf('%s %s %s', substr($digits, 0, 4), substr($digits, 4, 3), substr($digits, 7, 3));
        }

        return sprintf('%s %s %s', substr($digits, 0, 2), substr($digits, 2, 4), substr($digits, 6, 4));
    }
}
