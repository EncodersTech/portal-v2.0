<?php

namespace App\Models;

use App\Traits\Excludable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    use Excludable;

    public $table = 'conversations';

    const CONVERSATION = 1;

    protected $fillable = [
        'message',
        'to_id',
        'from_id',
        'is_host',
        'gym_id',
    ];

    /**
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'from_id');
    }

    /**
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'to_id');
    }

    /**
     * @return BelongsTo
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }
}
