<?php

namespace App\Models;

use App\Helpers\Traits\HasFile;
use App\Helpers\Traits\ListableTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable implements MustVerifyEmail
{
    use HasFile;
    use Notifiable;
    use ListableTrait;

    public const TYPE_ADMIN = "admin";
    public const TYPE_EDITOR = "editor";
    public const TYPE_CLIENT = "client";

    public const STATUS_ACTIVE = "active";
    public const STATUS_BLOCKED = "blocked";
    public const STATUS_UNVERIFIED = "unverified";

    /**
     * The "root" path where file related to this model will be stored
     *
     * @var string
     */
    protected $rootDirPath = "members";

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ["id", 'password', 'remember_token'];

    /**
     * Append extra attributes to model
     *
     * @var array
     */
    protected $appends = ["avatar"];

    /**
     * @return string|null
     */
    public function getAvatarAttribute(): ?string
    {
        return $this->uuid ? jc()->signedRoute("avatar", $this->uuid) : null;
    }

    /**
     * @param bool $makeDir
     * @return string
     * @throws \Exception
     */
    public function getAvatarPath(bool $makeDir = true): string
    {
        return $this->createPath("avatar.png", null, $makeDir);
    }

    /**
     * @return string
     */
    public function getGravatarLink(): string
    {
        $emailHash = md5(strtolower(trim($this->email)));
        return "https://secure.gravatar.com/avatar/{$emailHash}?s=120&d=retro";
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where("status", self::STATUS_ACTIVE);
    }

    /**
     * @param string $date
     * @return string
     */
    public function getDateAttribute(string $date): string
    {
        return Carbon::parse($date)->format("d M, Y");
    }

    /**
     * Checks if member is Active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Checks if member is Blocked
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    /**
     * Checks if member is Unverified
     *
     * @return bool
     */
    public function isUnverified(): bool
    {
        return $this->status === self::STATUS_UNVERIFIED;
    }

    /**
     * @return bool
     */
    public function isClient(): bool
    {
        return $this->type === self::TYPE_CLIENT;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    /**
     * @return bool
     */

    public function isEditor(): bool
    {
        return $this->type === self::TYPE_EDITOR;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getMetadata(string $name)
    {
        $metaData = $this->metaData()->where("name", $name)->first("value");

        return $metaData->value ?? null;
    }

    /**
     * Get the meta-data belonging to the member
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|MemberMeta
     */
    public function metaData()
    {
        return $this->hasMany(MemberMeta::class, 'member_id', 'id');
    }

    /**
     * @param Builder $query
     * @param Carbon  $startDate
     * @param Carbon  $endDate
     * @return Builder
     */
    public function scopeWhereCreatedBetween(Builder $query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween(DB::raw("DATE(created_at)"), array(
            $startDate->format("Y-m-d"), $endDate->format("Y-m-d")
        ));
    }
}
