<?php

namespace App\Models;

use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property int $boardroom_id
 * @property int $created_by
 * @property Carbon $meeting_date
 * @property string $start_time
 * @property string $end_time
 * @property string $status
 * @property-read Boardroom $boardroom
 * @property-read User $creator
 */
#[Fillable([
    'title',
    'agenda',
    'minutes',
    'action_items',
    'boardroom_id',
    'created_by',
    'meeting_date',
    'start_time',
    'end_time',
    'status',
])]
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * Get the boardroom reserved for this meeting.
     *
     * @return BelongsTo<Boardroom, $this>
     */
    public function boardroom()
    {
        return $this->belongsTo(Boardroom::class);
    }

    /**
     * Get the user who created this meeting.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function documents()
    {
        return $this->hasMany(MeetingDocument::class);
    }

    public function rsvps()
    {
        return $this->hasMany(MeetingRsvp::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }
}
