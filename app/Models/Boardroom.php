<?php

namespace App\Models;

use Database\Factories\BoardroomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $capacity
 * @property string $location
 * @property string $status
 * @property string|null $amenities
 * @property-read Collection<int, Meeting> $meetings
 */
#[Fillable(['name', 'capacity', 'location', 'status', 'amenities'])]
class Boardroom extends Model
{
    /** @use HasFactory<BoardroomFactory> */
    use HasFactory;

    /**
     * Get the meetings booked in this boardroom.
     *
     * @return HasMany<Meeting, $this>
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
