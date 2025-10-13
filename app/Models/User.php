<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'invited_at',
        'invitation_code',
    ];

    public function postVisits(): HasMany
    {
        return $this->hasMany(UserPostVisit::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function addPostVisit(Post $post): void
    {
        UserPostVisit::updateOrCreate([
            'user_id' => $this->id,
            'post_id' => $post->id,
        ], [
            'visited_at' => now(),
        ]);
    }

    public function lastSeenAt(Post|int $postId): ?Carbon
    {
        if ($postId instanceof Post) {
            $postId = $postId->id;
        }

        $latestPostVisit = $this->postVisits
            ->first(fn (UserPostVisit $postVisit) => $postVisit->post_id === $postId);

        return $latestPostVisit?->visited_at;
    }

    public function hasUnreadComments(Post $post): bool
    {
        if (! $post->last_comment_at) {
            return false;
        }

        $lastSeenAt = $this->lastSeenAt($post);

        if (! $lastSeenAt) {
            return true;
        }

        return $lastSeenAt->lt($post->last_comment_at);
    }

    public function hasSeenComment(PostComment $comment, ?Carbon $lastSeenAt = null): bool
    {
        $lastSeenAt ??= $this->lastSeenAt($comment->post_id);

        if (! $lastSeenAt) {
            return true;
        }

        return $lastSeenAt->gte($comment->created_at);
    }
}
