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
use App\Models\Traits\Translated;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Request;
use Prologue\Alerts\Facades\Alert;

class Category extends BaseModel
{
    use Sluggable, SluggableScopeHelpers, Translated;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';
    
    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = 'id';
    protected $appends = ['tid'];
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;
    
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
        'parent_id',
        'name',
        'slug',
        'description',
        'picture',
        'css_class',
        'active',
        'lft',
        'rgt',
        'depth',
        'translation_lang',
        'translation_of',
        'type'
    ];
    public $translatable = ['name', 'slug', 'description'];
    
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
    // protected $dates = [];
    
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
        static::deleting(function ($category) {
            // Delete all translated categories
            $category->translated()->delete();

            // Delete all category ads
            $ads = Ad::where('category_id', $category->id)->get();
            if (!empty($ads)) {
                foreach ($ads as $ad) {
                    $ad->delete();
                }
            }

            // Don't delete the default pictures
            $defaultPicture = 'app/default/categories/fa-folder-' . config('settings.app_theme', 'default') . '.png';
            if (!str_contains($category->picture, $defaultPicture)) {
                // Delete the category picture
                Storage::delete($category->picture);
            }

            // If the category is a parent category, delete all its children
            if ($category->parent_id == 0) {
                $cats = self::where('parent_id', $category->id)->get();
                if (!empty($cats)) {
                    foreach ($cats as $cat) {
                        $cat->delete();
                    }
                }
            }
        });
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'slug_or_name',
            ],
        ];
    }

    public function getNameHtml()
    {
        return '<a href="category/' . $this->id . '/sub_category">' . $this->name . '</a>';
    }
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function ads()
    {
        return $this->hasManyThrough('App\Models\Ad', 'App\Models\Category', 'parent_id', 'category_id');
    }
    
    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id');
    }
    
    public function lang()
    {
        return $this->hasOne('App\Models\Category', 'translation_of', 'abbr');
    }
    
    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id');
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
    // The slug is created automatically from the "title" field if no slug exists.
    public function getSlugOrNameAttribute()
    {
        if ($this->slug != '') {
            return $this->slug;
        }
        return $this->name;
    }

    /**
     * Category icons pictures from original version
     * Only the file name is set in Category 'picture' field
     * Example: fa-car.png
     *
     * @return null|string
     */
    public function getPictureFromOriginPath()
    {
        if (!isset($this->attributes) || !isset($this->attributes['picture'])) {
            return null;
        }

        $value = $this->attributes['picture'];
        if (empty($value)) {
            return null;
        }

        // Fix path
        $value = 'app/categories/' . config('settings.app_theme', 'default') . '/' . $value;

        if (!Storage::exists($value)) {
            $value = null;
        }

        return $value;
    }

    public function getPictureAttribute()
    {
        // OLD PATH
        $value = $this->getPictureFromOriginPath();
        if (!empty($value)) {
            return $value;
        }

        // NEW PATH
        if (!isset($this->attributes) || !isset($this->attributes['picture'])) {
            $value = 'app/default/categories/fa-folder-' . config('settings.app_theme', 'default') . '.png';
            return $value;
        }

        $value = $this->attributes['picture'];

        if (!Storage::exists($value)) {
            $value = 'app/default/categories/fa-folder-' . config('settings.app_theme', 'default') . '.png';
        }

        return $value;
    }
    
    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setPictureAttribute($value)
    {
        $attribute_name = 'picture';
        $destination_path = 'app/categories/custom';

        // If the image was erased
        if (empty($value)) {
            // Don't delete the default pictures
            $defaultPicture = 'app/default/categories/fa-folder-' . config('settings.app_theme', 'default') . '.png';
            if (!str_contains($this->picture, $defaultPicture)) {
                // delete the image from disk
                Storage::delete($this->picture);
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
        if (starts_with($value, 'data:image')) {
            try {
                // Make the image
                $image = Image::make($value)->resize(400, 400, function($constraint) {
                    $constraint->aspectRatio();
                });
            } catch (\Exception $e) {
                Alert::error($e->getMessage())->flash();
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

    /**
     * Activate/Deactivate categories with their children if exist
     * Activate/Deactivate translated entries with their translations if exist
     * @param $value
     */
    public function setActiveAttribute($value)
    {
        $entityId = (isset($this->attributes['id'])) ? $this->attributes['id'] : null;

        if (!empty($entityId)) {
            // Activate the entry
            $this->attributes['active'] = $value;

            // If the entry is a parent entry, activate its children
            $parentId = (isset($this->attributes['parent_id'])) ? $this->attributes['parent_id'] : null;
            if ($parentId == 0) {
                // ... AND don't select the current parent entry to prevent infinite recursion
                $entries = $this->where('parent_id', $entityId)->get();
                if (!empty($entries)) {
                    foreach ($entries as $entry) {
                        $entry->active = $value;
                        $entry->save();
                    }
                }
            }
        } else {
            // Activate the new entries
            $this->attributes['active'] = $value;
        }
    }
}
