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

namespace App\Http\Controllers\Admin;

use App\Models\AdType;
use App\Models\Category;
use Illuminate\Support\Facades\Input;
use App\Models\SalaryType;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\AdRequest as StoreRequest;
use App\Http\Requests\Admin\AdRequest as UpdateRequest;

class AdController extends PanelController
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->xPanel->setModel('App\Models\Ad');
        $this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/ad');
        $this->xPanel->setEntityNameStrings(__t('ad'), __t('ads'));
        $this->xPanel->enableAjaxTable();
        $this->xPanel->denyAccess(['create']);
        $this->xPanel->orderBy('created_at', 'DESC');

        // Filters
        if (Input::has('active')) {
            if (Input::get('active') == 0) {
                $this->xPanel->addClause('where', 'active', '=', 0);
                if (config('settings.ads_review_activation')) {
                    $this->xPanel->addClause('orWhere', 'reviewed', '=', 0);
                }
            }
            if (Input::get('active') == 1) {
                $this->xPanel->addClause('where', 'active', '=', 1);
                if (config('settings.ads_review_activation')) {
                    $this->xPanel->addClause('where', 'reviewed', '=', 1);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | COLUMNS AND FIELDS
        |--------------------------------------------------------------------------
        */
        // COLUMNS
        $this->xPanel->addColumn([
            'name'  => 'created_at',
            'label' => __t("Date"),
            'type'  => 'date',
        ]);
        $this->xPanel->addColumn([
            'name'          => 'title',
            'label'         => __t('Title'),
            'type'          => 'model_function',
            'function_name' => 'getTitleHtml',
        ]);
        $this->xPanel->addColumn([
            'name'          => 'logo', // Put unused field column
            'label'         => __t("Logo"),
            'type'          => 'model_function',
            'function_name' => 'getLogoHtml',
        ]);
        $this->xPanel->addColumn([
            'name'  => 'company_name',
            'label' => __t("Company Name"),
        ]);
        $this->xPanel->addColumn([
            'name'          => 'country_code',
            'label'         => __t("Country"),
            'type'          => 'model_function',
            'function_name' => 'getCountryHtml',
        ]);
        $this->xPanel->addColumn([
            'name'          => 'city_id',
            'label'         => __t("City"),
            'type'          => 'model_function',
            'function_name' => 'getCityHtml',
        ]);
        $this->xPanel->addColumn([
            'name'          => 'active',
            'label'         => __t("Active"),
            'type'          => 'model_function',
            'function_name' => 'getActiveHtml',
        ]);
        $this->xPanel->addColumn([
            'name'          => 'reviewed',
            'label'         => __t("Reviewed"),
            'type'          => "model_function",
            'function_name' => 'getReviewedHtml',
        ]);

        // FIELDS
        $this->xPanel->addField([
            'label'       => __t("Category"),
            'name'        => 'category_id',
            'type'        => 'select_from_array',
            'options'     => $this->categories(),
            'allows_null' => false,
        ]);
        $this->xPanel->addField([
            'name'       => 'company_name',
            'label'      => __t('Company Name'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Company Name'),
            ],
        ]);
        $this->xPanel->addField([
            'name'   => 'logo',
            'label'  => __t('Logo') . ' (Supported file extensions: jpg, jpeg, png, gif)',
            'type'   => 'image',
            'upload' => true,
            'disk'   => 'uploads',
        ]);
        $this->xPanel->addField([
            'name'       => 'company_description',
            'label'      => __t("Company Description"),
            'type'       => 'textarea',
            'attributes' => [
                'placeholder' => __t("Company Description"),
                'rows' => 10,
            ],
        ]);
        $this->xPanel->addField([
            'label'       => __t("Ad Type"),
            'name'        => 'ad_type_id',
            'type'        => 'select_from_array',
            'options'     => $this->adType(),
            'allows_null' => false,
        ]);
        $this->xPanel->addField([
            'name'       => 'title',
            'label'      => __t('Title'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Title'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'description',
            'label'      => __t("Description"),
            'type'       => (config('settings.simditor_wysiwyg')) ? 'simditor' : ((!config('settings.simditor_wysiwyg') && config('settings.ckeditor_wysiwyg')) ? 'ckeditor' : 'textarea'),
            'attributes' => [
                'placeholder' => __t("Description"),
                'id'          => 'description',
                'rows'        => 10,
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'salary_min',
            'label'      => __t("Salary (min)"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t("Salary (min)"),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'salary_max',
            'label'      => __t("Salary (max)"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t("Salary (max)"),
            ],
        ]);
        $this->xPanel->addField([
            'label'       => __t("Salary Type"),
            'name'        => 'salary_type_id',
            'type'        => 'select_from_array',
            'options'     => $this->salaryType(),
            'allows_null' => false,
        ]);
        $this->xPanel->addField([
            'name'  => 'negotiable',
            'label' => __t("Negotiable Salary"),
            'type'  => 'checkbox',
        ]);

        $this->xPanel->addField([
            'name'       => 'contact_name',
            'label'      => __t('User Name'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('User Name'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'contact_email',
            'label'      => __t('User Email'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('User Email'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'contact_phone',
            'label'      => __t('User Phone'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('User Phone'),
            ],
        ]);
        $this->xPanel->addField([
            'name'  => 'contact_phone_hidden',
            'label' => __t("Hide contact phone"),
            'type'  => 'checkbox',
        ]);
        $this->xPanel->addField([
            'name'       => 'company_website',
            'label'      => __t('Company Website'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Company Website'),
            ],
        ]);
        /*$this->xPanel->addField([
            'name' => 'address',
            'label' => __t('Address'),
            'type' => 'text',
            'attributes' => [
                'placeholder' => __t('Address'),
            ],
        ]);*/
        $this->xPanel->addField([
            'name'  => 'active',
            'label' => __t("Active"),
            'type'  => 'checkbox',
        ]);
        $this->xPanel->addField([
            'name'  => 'reviewed',
            'label' => __t("Reviewed"),
            'type'  => 'checkbox',
        ]);
        $this->xPanel->addField([
            'name'  => 'featured',
            'label' => __t("Featured"),
            'type'  => 'checkbox',
        ]);
        $this->xPanel->addField([
            'name'  => 'archived',
            'label' => __t("Archived"),
            'type'  => 'checkbox',
        ]);
    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud();
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud();
    }

    public function adType()
    {
        $entries = AdType::where('translation_lang', config('app.locale'))->get();
        if (empty($entries)) {
            return [];
        }

        $tab = [];
        foreach ($entries as $entry) {
            $tab[$entry->translation_of] = $entry->name;
        }

        return $tab;
    }

    public function categories()
    {
        $entries = Category::where('translation_lang', config('app.locale'))->where('parent_id', 0)->orderBy('lft')->get();
        if (empty($entries)) {
            return [];
        }

        $tab = [];
        foreach ($entries as $entry) {
            $tab[$entry->translation_of] = $entry->name;

            $subEntries = Category::where('translation_lang', config('app.locale'))->where('parent_id', $entry->id)->orderBy('lft')->get();
            if (!empty($subEntries)) {
                foreach ($subEntries as $subEntrie) {
                    $tab[$subEntrie->translation_of] = "---| " . $subEntrie->name;
                }
            }
        }

        return $tab;
    }

    public function salaryType()
    {
        $entries = SalaryType::where('translation_lang', config('app.locale'))->get();
        if (empty($entries)) {
            return [];
        }

        $tab = [];
        foreach ($entries as $entry) {
            $tab[$entry->translation_of] = $entry->name;
        }

        return $tab;
    }
}
