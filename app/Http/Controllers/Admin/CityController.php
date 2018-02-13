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

use App\Models\City;
use App\Models\Scopes\ActiveScope;
use Larapen\Admin\app\Http\Controllers\PanelController;
use App\Http\Requests\Admin\CityRequest as StoreRequest;
use App\Http\Requests\Admin\CityRequest as UpdateRequest;

class CityController extends PanelController
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->xPanel->setModel('App\Models\City');
        $this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/city');
        $this->xPanel->setEntityNameStrings(__t('city'), __t('cities'));
        $this->xPanel->enableAjaxTable();

        /*
        |--------------------------------------------------------------------------
        | COLUMNS AND FIELDS
        |--------------------------------------------------------------------------
        */
        // COLUMNS
        $this->xPanel->addColumn([
            'name'  => 'country_code',
            'label' => __t("Country Code"),
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
            'name'  => 'subadmin1_code',
            'label' => __t("Admin1 Code"),
        ]);
        $this->xPanel->addColumn([
            'name'  => 'subadmin2_code',
            'label' => __t("Admin2 Code"),
        ]);
        $this->xPanel->addColumn([
            'name'          => 'active',
            'label'         => __t("Active"),
            'type'          => 'model_function',
            'function_name' => 'getActiveHtml',
        ]);

        // FIELDS
        $this->xPanel->addField([
            'name'    => 'id',
            'type'    => 'hidden',
            'default' => $this->autoIncrementId(),
        ]);
        $this->xPanel->addField([
            'name'       => 'country_code',
            'label'      => __t('Country Code'),
            'type'       => 'select2',
            'attribute'  => 'asciiname',
            'model'      => 'App\Models\Country',
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
            'name'       => 'latitude',
            'label'      => __t("Latitude"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t("Latitude"),
            ],
            'hint'       => __t('In decimal degrees (wgs84)'),
        ]);
        $this->xPanel->addField([
            'name'       => 'longitude',
            'label'      => __t("Longitude"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t("Longitude"),
            ],
            'hint'       => __t('In decimal degrees (wgs84)'),
        ]);
        $this->xPanel->addField([
            'name'       => 'subadmin1_code',
            'label'      => __t("Admin1 Code"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the admin1 code (example: 08) without the country code'),
            ],
            'hint'       => __t('Code for the first administrative division. Please check the admin1 code format here:') . ' <a href="http://download.geonames.org/export/dump/admin1CodesASCII.txt" target="_blank">http://download.geonames.org/export/dump/admin1CodesASCII.txt</a>',
        ]);
        $this->xPanel->addField([
            'name'       => 'subadmin2_code',
            'label'      => __t("Admin2 Code"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Optional') . ' - ' . __t('Enter the admin2 code (example: 5883638) without the admin1 code'),
            ],
            'hint'       => __t('Code for the second administrative division. Please check the admin2 code format here:') . ' <a href="http://download.geonames.org/export/dump/admin2Codes.txt" target="_blank">http://download.geonames.org/export/dump/admin2Codes.txt</a>',
        ]);
        $this->xPanel->addField([
            'name'       => 'population',
            'label'      => __t("Population"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t("Population"),
            ],
        ]);
        $this->xPanel->addField([
            'name'       => 'time_zone',
            'label'      => __t("Time Zone ID"),
            'type'       => 'text',
            'attributes' => [
                'placeholder' => __t('Enter the time zone ID (example: Europe/Paris)'),
            ],
            'hint'       => __t('Please check the TimeZoneId code format here:') . ' <a href="http://download.geonames.org/export/dump/timeZones.txt" target="_blank">http://download.geonames.org/export/dump/timeZones.txt</a>',
        ]);
        $this->xPanel->addField([
            'name'  => 'active',
            'label' => __t("Active"),
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

    /**
     * Increment new cities IDs
     *
     * @return int
     */
    public function autoIncrementId()
    {
        // Note: 10793747 is the higher ID found in Geonames cities database
        // To guard against any MySQL error we will increment new IDs from 14999999
        $startId = 14999999;

        // Count all non-Geonames entries
        $addedCities = City::withoutGlobalScope(ActiveScope::class)->where('id', '>=', $startId)->orderBy('id', 'DESC')->first();
        $lastAddedId = (!empty($addedCities)) ? $addedCities->id : $startId;

        // Set new ID
        $newId = $lastAddedId + 1;

        return $newId;
    }
}
