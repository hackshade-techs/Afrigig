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

use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\CountryRequest as StoreRequest;
use App\Http\Requests\Admin\CountryRequest as UpdateRequest;

class CountryController extends PanelController
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->xPanel->setModel('App\Models\Country');
        $this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/country');
        $this->xPanel->setEntityNameStrings(__t('country'), __t('countries'));
        $this->xPanel->enableAjaxTable();

        /*
        |--------------------------------------------------------------------------
        | COLUMNS AND FIELDS
        |--------------------------------------------------------------------------
        */
        // COLUMNS
        $this->xPanel->addColumn([
            'name'  => 'code',
            'label' => __t("Code"),
        ]);
        $this->xPanel->addColumn([
            'name'  => 'name',
            'label' => __t("Local Name"),
        ]);
        $this->xPanel->addColumn([
            'name'  => 'asciiname',
            'label' => __t("Name"),
        ]);
        $this->xPanel->addColumn([
            'name'  => 'tld',
            'label' => __t("TLD"),
        ]);
        $this->xPanel->addColumn([
            'name'  => 'languages',
            'label' => __t("Languages"),
        ]);
        $this->xPanel->addColumn([
            'name'          => 'active',
            'label'         => __t("Active"),
            'type'          => 'model_function',
            'function_name' => 'getActiveHtml',
        ]);

        // FIELDS
        $this->xPanel->addField([
            'name'       => 'code',
            'label'      => __t('Code'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the country code (ISO Code)'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'name',
            'label'      => __t('Local Name'),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Local Name'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'asciiname',
            'label'      => __t("Name"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the country name (In English)'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'capital',
            'label'      => __t('Capital') . ' (' . __t('Optional') . ')',
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Capital'),
            ],
        ]);
        $this->xPanel->addField([
            'name'      => 'continent_code',
            'label'     => __t('Continent'),
            'type'      => 'select2',
            'attribute' => 'name',
            'model'     => 'App\Models\Continent',
        ]);
        $this->xPanel->addField([
            'name'       => 'tld',
            'label'      => __t('TLD') . ' (' . __t('Optional') . ')',
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the country tld (E.g. .bj for Benin)'),
            ],
        ]);
        $this->xPanel->addField([
            'name'      => 'currency_code',
            'label'     => __t("Currency Code"),
            'type'      => 'select2',
            'attribute' => 'name',
            'model'     => 'App\Models\Currency',
        ]);
        $this->xPanel->addField([
            'name'       => 'phone',
            'label'      => __t("Phone Ind.") . ' (' . __t('Optional') . ')',
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the country phone ind. (E.g. +229 for Benin)'),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'languages',
            'label'      => __t("Languages"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the locale code (ISO) separate with comma'),
            ],
        ]);
        /*
        $this->xPanel->addField([
            'name'  => 'active',
            'label' => __t("Active"),
            'type'  => 'checkbox',
        ]);
        */
    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud();
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud();
    }
}
