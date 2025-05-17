<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Task extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'status',
        'due_date',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'date',
    ];


    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE = 'done';


    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function isOverdue()
    {
        return $this->status !== self::STATUS_DONE &&
               $this->due_date->isPast();
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', self::STATUS_DONE)
                    ->where('due_date', '<', now());
    }


    public function scopeForRole($query, User $user)
    {
        if ($user->isAdmin()) {
            return $query;
        } elseif ($user->isManager()) {

            $staffUsers = User::where('role', User::ROLE_STAFF)->pluck('id');
            return $query->where('created_by', $user->id)
                        ->orWhereIn('assigned_to', $staffUsers);
        } else {

            return $query->where('assigned_to', $user->id)
                        ->orWhere('created_by', $user->id);
        }
    }
}
