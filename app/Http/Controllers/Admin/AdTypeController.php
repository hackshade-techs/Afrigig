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
use App\Http\Requests\Admin\AdTypeRequest as StoreRequest;
use App\Http\Requests\Admin\AdTypeRequest as UpdateRequest;

class AdTypeController extends PanelController
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->xPanel->setModel('App\Models\AdType');
        $this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/item_type');
        $this->xPanel->setEntityNameStrings(__t('ad type'), __t('ad types'));
        $this->xPanel->enableReorder('name', 2);
        $this->xPanel->enableDetailsRow();
        $this->xPanel->allowAccess(['reorder', 'details_row']);
        //$this->xPanel->denyAccess(['create', 'delete']);
        $this->xPanel->orderBy('lft', 'ASC');

        /*
        |--------------------------------------------------------------------------
        | COLUMNS AND FIELDS
        |--------------------------------------------------------------------------
        */
        // COLUMNS
        $this->xPanel->addColumn([
            'name'  => 'id',
            'label' => "ID",
        ]);
        $this->xPanel->addColumn([
            'name'  => 'name',
            'label' => __t('Name'),
        ]);
        $this->xPanel->addColumn([
            'name'          => 'active',
            'label'         => __t("Active"),
            'type'          => 'model_function',
            'function_name' => 'getActiveHtml',
        ]);

        // FIELDS
        $this->xPanel->addField([
            'name'       => "name",
            'label'      => __t('Name'),
            'type'       => "text",
            'attributes' => [
                'placeholder' => __t('Name'),
            ],
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
}
