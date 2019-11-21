<?php

namespace CarlosCGO\Google2fa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PasswordSecurity
 * @package App\GraphQL\Models\User
 */
class User2fa extends Model
{
    /**
     * @var string
     */
    protected $table   = 'user_2fa';

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('screen2fa.models.user'));
    }
}
