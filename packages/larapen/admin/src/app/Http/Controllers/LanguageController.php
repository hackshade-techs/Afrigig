<?php
/**
 * LaraClassified - Geo Classified Ads CMS
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

namespace Larapen\Admin\app\Http\Controllers;

use App\Http\Requests;
use App\Models\Ad;
use App\Models\Language;
use App\Models\Payment;
use App\Models\User;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ReviewedScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\Admin\LanguageRequest as StoreRequest;
use App\Http\Requests\Admin\LanguageRequest as UpdateRequest;

class LanguageController extends PanelController
{
    // Translated models with their relations
    // @todo: get this array dynamically from Models folder
    protected $translatedModels = [
        'AdType'   => [['name' => 'Ad', 'key' => 'ad_type_id']],
        'Category' => [
            ['name' => 'Category', 'key' => 'parent_id'],
            ['name' => 'Ad', 'key' => 'category_id']],
        'Gender'   => [['name' => 'User', 'key' => 'gender_id']],
        'Package'  => [['name' => 'Payment', 'key' => 'package_id']],
        'ReportType',
        'Page',
        'SalaryType' => [['name' => 'Ad', 'key' => 'salary_type_id']],
    ];

    // Get models namespace
    protected $namespace = '\\App\Models\\';


    /**
     * LanguageController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->xPanel->setModel('App\Models\Language');
        $this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/language');
        $this->xPanel->setEntityNameStrings(__t('language'), __t('languages'));

        /*
        |--------------------------------------------------------------------------
        | COLUMNS AND FIELDS
        |--------------------------------------------------------------------------
        */
        // COLUMNS
        $this->xPanel->addColumn([
            'name'  => 'name',
            'label' => trans('admin::messages.language_name'),
        ]);
        $this->xPanel->addColumn([
            'name'          => 'active',
            'label'         => trans('admin::messages.active'),
            'type'          => "model_function",
            'function_name' => 'getActiveHtml',
        ]);
        $this->xPanel->addColumn([
            'name'          => 'default',
            'label'         => trans('admin::messages.default'),
            'type'          => "model_function",
            'function_name' => 'getDefaultHtml',
        ]);

        // FIELDS
        $this->xPanel->addField([
            'name'       => 'name',
            'label'      => trans('admin::messages.language_name'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => trans('admin::messages.language_name'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'native',
            'label'      => trans('admin::messages.native_name'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => trans('admin::messages.native_name'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'abbr',
            'label'      => trans('admin::messages.code_iso639-1'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => trans('admin::messages.code_iso639-1'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'locale',
            'label'      => __t('Locale Code (E.g. en_US)'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Locale Code (E.g. en_US)'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'script',
            'label'      => __t('Script'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the script code (latn, etc.)'),
            ],
        ]);
        $this->xPanel->addField([
            'name'  => 'russian_pluralization',
            'label' => __t('Russian Pluralization'),
            'type'  => 'checkbox',
        ]);
        $this->xPanel->addField([
            'name'  => 'active',
            'label' => trans('admin::messages.active'),
            'type'  => 'checkbox',
        ]);
        $this->xPanel->addField([
            'name'  => 'default',
            'label' => trans('admin::messages.default'),
            'type'  => 'checkbox',
        ], 'update');
    }

    public function store(StoreRequest $request)
    {
        $defaultLang = Language::where('default', 1)->first();

        // Copy the english language folder to the new language folder
        \File::copyDirectory(resource_path('lang/' . $defaultLang->abbr), resource_path('lang/' . $request->input('abbr')));
        \File::copyDirectory(resource_path('lang/vendor/admin/' . $defaultLang->abbr), resource_path('lang/vendor/admin/' . $request->input('abbr')));

        // Create translated entries
        $this->createTranslatedEntries($defaultLang->abbr, $request->input('abbr'));

        return parent::storeCrud();
    }

    /**
     * Create translated entries
     *
     * @param $defaultLangAbbr
     * @param $abbr
     */
    public function createTranslatedEntries($defaultLangAbbr, $abbr)
    {
        // Create Translated Models entries
        foreach($this->translatedModels as $name => $relations) {
            // Fix models without relations
            if (is_numeric($name) && is_string($relations)) {
                $name = $relations;
            }
            $model = $this->namespace . $name;

            // Get the model's main entries
            $mainEntries = $model::where('translation_lang', strtolower($defaultLangAbbr))->get();
            if ($mainEntries->count() > 0) {
                foreach($mainEntries as $entry) {
                    $newEntryInfo = $entry->toArray();
                    $newEntryInfo['translation_lang'] = strtolower($abbr);

                    // Save newEntry to database
                    $newEntry = new $model($newEntryInfo);
                    $newEntry->save();
                }
            }
        }
    }

    public function update(UpdateRequest $request)
    {
        // Set default language
        if (Input::has('default')) {
            if (Input::get('default') == 1 || Input::get('default') == 'on') {
                // Update translated entries
                $this->updateTranslatedEntries(Input::get('abbr'));
                // Set default language
                $this->setDefaultLanguage(Input::get('abbr'));
            }
        }

        return parent::updateCrud();
    }

    /**
     * Update translated entries
     * @param $abbr
     */
    public function updateTranslatedEntries($abbr)
    {
        // Update Translated Models entries
        foreach ($this->translatedModels as $name => $relations) {
            // Fix models without relations
            if (is_numeric($name) && is_string($relations)) {
                $name = $relations;
            }
            $model = $this->namespace . $name;

            // Get new "translation_of" value with old entries
            $tmpEntries = $model::where('translation_lang', strtolower($abbr))->get();
            $newTid = [];
            if ($tmpEntries->count() > 0) {
                foreach($tmpEntries as $tmp) {
                    $newTid[$tmp->translation_of] = $tmp->id;
                }
            }

            // Change "translation_of" value with new Default Language
            $entries = $model::all();
            if ($entries->count() > 0) {
                foreach($entries as $entry) {
                    if (isset($newTid[$entry->translation_of])) {
                        $entry->translation_of = $newTid[$entry->translation_of];
                        $entry->save();
                    }
                }
            }

            // If relation exists, change its foreign key value
            if (isset($relations) && is_array($relations) && !empty($relations)) {
                foreach ($relations as $relation) {
                    if (!isset($relation) || !isset($relation['key']) || !isset($relation['name'])) {
                        continue;
                    }
                    $relModel = $this->namespace . $relation['name'];
                    $relEntries = $relModel::all();
                    if ($relEntries->count() > 0) {
                        foreach ($relEntries as $relEntry) {
                            if (isset($newTid[$relEntry->{$relation['key']}])) {
                                // Update the relation entry
                                $relEntry->{$relation['key']} = $newTid[$relEntry->{$relation['key']}];
                                $relEntry->save();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Set default language (Call this method at last)
     * @param $abbr
     */
    public function setDefaultLanguage($abbr)
    {
        // Unset the old default language
        Language::whereIn('active', [0, 1])->update(['default' => 0]);

        // Set the new default language
        Language::where('abbr', $abbr)->update(['default' => 1]);
    }

    /**
     * After delete remove also the language folder.
     *
     * @param $id
     * @param null $childId
     * @return mixed
     */
    public function destroy($id, $childId = null)
    {
        if (!empty($childId)) {
            $id = $childId;
        }

        // Get the language
        $language = Language::find($id);
        if (empty($language)) {
            return false;
        }

        // Don't delete the default language
        if ($language->abbr == config('applang.abbr')) {
            return false;
        }

        // Delete the language
        $destroyResult = parent::destroy($id);

        if ($destroyResult) {
            // Delete all translated entries
            $this->destroyTranslatedEntries($language->abbr);

            // Remove all language files
            \File::deleteDirectory(resource_path('lang/' . $language->abbr));
            \File::deleteDirectory(resource_path('lang/vendor/admin/' . $language->abbr));
        }

        return $destroyResult;
    }

    /**
     * Delete translated entries
     *
     * @param $abbr
     */
    public function destroyTranslatedEntries($abbr)
    {
        // Remove Translated Models entries
        foreach($this->translatedModels as $name => $relations) {
            // Fix models without relations
            if (is_numeric($name) && is_string($relations)) {
                $name = $relations;
            }
            $model = $this->namespace . $name;

            // Get the model's main entries
            $translatedEntries = $model::where('translation_lang', strtolower($abbr))->get();
            if ($translatedEntries->count() > 0) {
                foreach($translatedEntries as $entry) {
                    // Delete
                    $entry->delete();
                }
            }
        }
    }
}
