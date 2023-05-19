<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\Source;
use App\Models\Category;
use App\Models\UserSource;
use App\Models\UserCategory;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'email_verified_at'
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

    protected static function boot()
    {
        parent::boot();

        // Newly registerd users should have access to all categories and sources 
        static::created(function ($user) {
            DB::transaction(function () use ($user) {
                $sources = Source::get();
                $categories = Category::get();

                foreach($sources as $source) {
                    UserSource::create([
                        'user_id' => $user->id,
                        'source_id' => $source->id
                    ]);
                }

                foreach($categories as $category) {
                    UserCategory::create([
                        'user_id' => $user->id,
                        'category_id' => $category->id
                    ]);
                }
            });
        });
    }

    public function profileUrl()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('assets/img/img_settings_avatar.png');
    }

    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(Source::class, 'source_user');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_user');
    }
}
