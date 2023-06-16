<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Interest;
use App\Models\Medias;
use App\Models\Country;
use App\Models\State;
use App\Models\UserPost;
use App\Models\Move;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'phone_verified_at',
        'date_of_birth',
        'role',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    protected function role(): Attribute
    {
        return new Attribute(
            get: fn($value) => ['user', 'business', 'admin'][$value],
        );
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function interests(){
        return $this->belongsToMany(Interest::class);
    }

    public function profile_picture(){
        return $this->hasOne(Medias::class, 'user_id', 'id')
                    ->where('file_type', 'image')
                    ->where('source_type', 'user_profile')
                    ->where('is_active', 1);
    }

    public function banner_picture(){
        return $this->hasOne(Medias::class, 'user_id', 'id')
                    ->where('file_type', 'image')
                    ->where('source_type', 'user_banner')
                    ->where('is_active', 1);
    }

    public function followings(){
        return $this->belongsToMany(self::class, 'follower_user', 'follower_id', 'following_id')->with(['profile_picture']);
    }

    public function followers(){
        return $this->belongsToMany(self::class, 'follower_user', 'following_id', 'follower_id')->with(['profile_picture']);
    }

    public function blocked_users(){
        return $this->belongsToMany(self::class, 'block_user', 'user_id', 'blocked_user_id');
    }

    public function friends(){
        return $this->belongsToMany(self::class, 'friend_user', 'user_id', 'friend_id');
    }

    public function user_country(){
        return $this->hasOne(Country::class, 'id', 'country_id');
    }

    public function user_state(){
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function posts(){
        return $this->hasMany(UserPost::class, 'user_id', 'id')
                    ->with(['pictures', 'shared', 'created_by', 'liked_by_me'])
                    ->withCount(['likes', 'Comments', 'shared'])
                    ->where('status', '1');
    }

    public function moves(){
        return $this->hasMany(Move::class, 'user_id', 'id')
                    ->with(['banner', 'category', 'created_by', 'invitees'])
                    ->where('status', '1');
    }

    public function followed_by_me(){
        return $this->belongsToMany(self::class, 'follower_user', 'following_id', 'follower_id')
                    ->where('follower_id', auth('api')->user()->id);
    }

    public function moves_invited(){
        return $this->belongsToMany(Move::class, 'invitee_move', 'invitee_id', 'move_id')->withPivot('invite_status');
    }

    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'created_by');
    }
}
