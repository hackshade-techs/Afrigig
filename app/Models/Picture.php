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

use App\Models\Scopes\ReviewedScope;
use Illuminate\Support\Facades\Request;
use App\Models\Scopes\ActiveScope;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class Picture extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pictures';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    // public $timestamps = false;

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
    protected $fillable = ['ad_id', 'filename', 'active'];

    /**
     * The attributes that should be hidden for arrays
     *
     * @var array
     */
    // protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];


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
        static::deleting(function ($picture) {
            // Delete all pictures files
            if (!empty($picture->filename)) {
                $filepath = str_replace('uploads/', '', $picture->filename);
                //Storage::delete($filepath);

                // Delete the picture with its thumbs
                $filename = last(explode('/', $filepath));
                $files = Storage::files(dirname($filepath));
                if (!empty($files)) {
                    foreach($files as $file) {
                        // Don't delete the default picture
                        if (str_contains($file, config('larapen.core.picture.default'))) {
                            continue;
                        }
                        if (str_contains($file, $filename)) {
                            Storage::delete($file);
                        }
                    }
                }
            }
        });
    }

    public function getAdTitleHtml()
    {
        if ($this->ad) {
            return '<a href="/' . config('app.locale') . '/' . slugify($this->ad->title) . '/' . $this->ad->id . '.html" target="_blank">' . $this->ad->title . '</a>';
        } else {
            return 'no-link';
        }
    }

    public function getFilenameHtml()
    {
        // Get picture
        $out = '<img src="' . resize($this->filename, 'small') . '" style="width:auto; height:90px;">';

        return $out;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function ad()
    {
        return $this->belongsTo('App\Models\Ad', 'ad_id');
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
    public function getFilenameFromOldPath()
    {
        if (!isset($this->attributes) || !isset($this->attributes['filename'])) {
            return null;
        }

        $value = $this->attributes['filename'];

        // Fix path
        $value = str_replace('uploads/pictures/', '', $value);
        $value = str_replace('pictures/', '', $value);
        $value = 'pictures/' . $value;

        if (!Storage::exists($value)) {
            $value = null;
        }

        return $value;
    }

    public function getFilenameAttribute()
    {
        // OLD PATH
        $value = $this->getFilenameFromOldPath();
        if (!empty($value)) {
            return $value;
        }

        // NEW PATH
        if (!isset($this->attributes) || !isset($this->attributes['filename'])) {
            return null;
        }

        $value = $this->attributes['filename'];

        if (!Storage::exists($value)) {
            $value = config('larapen.core.picture.default');
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setFilenameAttribute($value)
    {
        $attribute_name = 'filename';

        if (empty($this->ad)) {
            $this->attributes[$attribute_name] = null;
        }

        // Get ad details
        $ad = Ad::withoutGlobalScopes([ActiveScope::class, ReviewedScope::class])->where('id', $this->ad_id)->first();
        if (empty($ad)) {
            $this->attributes[$attribute_name] = null;

            return false;
        }

        // Path
        $destination_path = 'files/' . strtolower($ad->country_code) . '/' . $ad->id;

        // If the image was erased
        if (empty($value)) {
            // delete the image from disk
            if (!str_contains($this->filename, config('larapen.core.picture.default'))) {
                Storage::delete($this->filename);
            }

            // set null in the database column
            $this->attributes[$attribute_name] = null;

            return false;
        }
    
        // Check the image file
        if ($value == url('/')) {
            $this->attributes[$attribute_name] = null;
        
            return false;
        }

        // If laravel request->file('filename') resource OR base64 was sent, store it in the db
        try {

            // Image default sizes
            $width = (int)config('larapen.core.picture.size.width', 1000);
            $height = (int)config('larapen.core.picture.size.height', 1000);

            // Make the image
            $image = Image::make($value)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });

            // Check and load Watermark plugin
            $plugin = plugin_load('watermark');
            if (!empty($plugin)) {
                $image = call_user_func($plugin->class . '::apply', $image);
                if (empty($image)) {
                    $this->attributes[$attribute_name] = null;

                    return false;
                }
            }

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
            $this->attributes[$attribute_name] = null;

            return false;
        }

        // Generate a filename.
        $filename = md5($value . time()) . '.jpg';

        // Store the image on disk.
        Storage::put($destination_path . '/' . $filename, $image->stream());

        // Save the path to the database
        $this->attributes[$attribute_name] = $destination_path . '/' . $filename;
    }
}
