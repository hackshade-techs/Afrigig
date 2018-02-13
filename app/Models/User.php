<?php
/**
 * JobClass - Geolocalized Job Board Script
 * Copyright (c) BedigitCom. All Rights Reserved
 *
 * Website: http://www.bedigit.com
 *
 * LICENSE
 * -------
 * This software is furnished under a license and may be used and copied
 * only in accordance with the terms of such license and with the inclusion
 * of the above copyright notice. If you Purchased from Codecanyon,
 * Please read the full License from here - http://codecanyon.net/licenses/standard
 */

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Request;

class User extends BaseUser
{
	use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'id';
    protected $appends = ['created_at_ta'];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_code',
        'user_type_id',
        'gender_id',
        'name',
        'about',
        'phone',
        'phone_hidden',
        'email',
        'password',
        'remember_token',
        'is_admin',
        'disable_comments',
        'receive_newsletter',
        'receive_advice',
        'ip_addr',
        'provider',
        'provider_id',
        'activation_token',
        'active',
        'blocked',
        'closed',
    ];
    
    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'last_login_at', 'deleted_at'];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope(new ActiveScope());
        
        // before delete() method call this
        static::deleting(function ($user) {
            // Delete all user's ads with dependencies
            if ($user->ads) {
                foreach ($user->ads as $item) {
                    $ad = Ad::find($item->id);
                    $ad->delete();
                }
            }
            
            // Delete all user's messages
            $user->messages()->delete();
            
            // Delete all favourite ads
            DB::table('saved_ads')->where('user_id', $user->id)->delete();
            
            // Delete all saved search
            $user->savedSearch()->delete();

            // Delete all user's resumes
            $resumes = Resume::where('user_id', $user->id)->get();
            if (!empty($resumes)) {
                foreach ($resumes as $resume) {
                    $resume->delete();
                }
            }
        });
    }

    public function getNameHtml()
    {
        // Pre URL (locale)
        $preUri = '';
        if (!(config('laravellocalization.hideDefaultLocaleInURL') == true && config('app.locale') == config('applang.abbr'))) {
            $preUri = config('app.locale') . '/';
        }

        // Get user search URL
        if (config('larapen.core.multi_countries_website')) {
            $url = url($preUri . trans('routes.v-search-user', ['countryCode' => strtolower($this->country_code), 'id' => $this->id]));
        } else {
            $url = url($preUri . trans('routes.v-search-user', ['id' => $this->id])) . '/?d=' . $this->country_code;
        }

        if (isset($this->ads) and $this->ads->count() > 0) {
            return '<a href="' . $url .'" target="_blank">' . $this->name . '</a>';
        } else {
            return $this->name;
        }
    }

    public function getCountryHtml()
    {
        $iconPath = 'images/flags/16/' . strtolower($this->country_code) . '.png';
        if (file_exists(public_path($iconPath))) {
            $out = '';
            $out .= '<a href="' . url('/') . '?d=' . $this->country_code . '" target="_blank">';
            $out .= '<img src="' . url($iconPath) . '" data-toggle="tooltip" title="' . $this->country_code . '">';
            $out .= '</a>';
            return $out;
        } else {
            return $this->country_code;
        }
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function ads()
    {
        return $this->hasMany('App\Models\Ad', 'user_id');
    }
    
    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_code');
    }
    
    public function gender()
    {
        return $this->belongsTo('App\Models\Gender', 'gender_id');
    }
    
    public function messages()
    {
        return $this->hasManyThrough('App\Models\Message', 'App\Models\Ad', 'user_id', 'ad_id');
    }
    
    public function savedAds()
    {
        return $this->belongsToMany('App\Models\Ad', 'saved_ads', 'user_id', 'ad_id');
    }
    
    public function savedSearch()
    {
        return $this->hasMany('App\Models\SavedSearch', 'user_id');
    }
    
    public function userType()
    {
        return $this->belongsTo('App\Models\UserType', 'user_type_id');
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    
    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */
    public function getCreatedAtAttribute($value)
    {
        $value = \Carbon\Carbon::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }
        //echo $value->format('l d F Y H:i:s').'<hr>'; exit();
        //echo $value->formatLocalized('%A %d %B %Y %H:%M').'<hr>'; exit(); // Multi-language

        return $value;
    }
    
    public function getUpdatedAtAttribute($value)
    {
        $value = \Carbon\Carbon::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }

        return $value;
    }
    
    public function getLastLoginAtAttribute($value)
    {
        $value = \Carbon\Carbon::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }

        return $value;
    }
    
    public function getDeletedAtAttribute($value)
    {
        $value = \Carbon\Carbon::parse($value);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }

        return $value;
    }
    
    public function getCreatedAtTaAttribute($value)
    {
        if (!isset($this->attributes['created_at']) and is_null($this->attributes['created_at'])) {
            return null;
        }

        $value = \Carbon\Carbon::parse($this->attributes['created_at']);
        if (config('timezone.id')) {
            $value->timezone(config('timezone.id'));
        }
        $value = time_ago($value, config('timezone.id'), config('app.locale'));

        return $value;
    }

    public function getEmailAttribute($value)
    {
        if (
            starts_with(getDomain(), config('larapen.core.demo_domain')) &&
            Request::segment(1) == config('larapen.admin.route_prefix', 'admin') &&
            Request::segment(2) != 'password'
        ) {

            $tmp = explode('@', $value);
            if (isset($tmp[0]) && isset($tmp[1])) {
                $emailUsername = $tmp[0];
                $emailDomain = $tmp[1];

                $hideStr = str_pad('', strlen($emailUsername) - 2, "x");
                $hideUsername = substr($emailUsername, 0, 1) . $hideStr . substr($emailUsername, -1);
                $value = $hideUsername . '@' . $emailDomain;
            }

            return $value;
        } else {
            return $value;
        }
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
