<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(string $action, Model $subject, string $description): void
    {
        self::create([
            'actor_id' => auth()->id(), 'action' => $action,
            'subject_type' => class_basename($subject), 'subject_id' => $subject->id,
            'description' => $description,
        ]);
    }
}
