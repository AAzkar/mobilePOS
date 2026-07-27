<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'currency_code',
        'currency_symbol',
        'default_tax_rate',
        'receipt_footer_text',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:2',
        ];
    }

    /**
     * The app has exactly one settings row; fetch it (creating defaults if missing).
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
