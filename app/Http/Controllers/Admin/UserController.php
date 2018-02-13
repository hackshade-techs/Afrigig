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

use App\Models\Gender;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Larapen\Admin\app\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\UserRequest as StoreRequest;
use App\Http\Requests\Admin\UserRequest as UpdateRequest;

class UserController extends PanelController
{
    public function __construct()
    {
        parent::__construct();

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->xPanel->setModel('App\Models\User');
        $this->xPanel->setRoute(config('larapen.admin.route_prefix', 'admin') . '/user');
        $this->xPanel->setEntityNameStrings(__t('user'), __t('users'));
        $this->xPanel->enableAjaxTable();
        $this->xPanel->orderBy('created_at', 'DESC');

        /*
        |--------------------------------------------------------------------------
        | COLUMNS AND FIELDS
        |--------------------------------------------------------------------------
        */
        if (Request::segment(2) != 'account') {
            // COLUMNS
            $this->xPanel->addColumn([
                'name'  => 'id',
                'label' => "ID",
            ]);
            $this->xPanel->addColumn([
                'name'  => 'name',
                'label' => __t("Name"),
            ]);
            $this->xPanel->addColumn([
                'name'  => 'email',
                'label' => __t("Email"),
            ]);
            $this->xPanel->addColumn([
                'label'         => __t("Country"),
                'name'          => 'country_code',
                'type'          => 'model_function',
                'function_name' => 'getCountryHtml',
            ]);
            $this->xPanel->addColumn([
                'name'      => 'user_type_id',
                'label'     => __t("Type"),
                'model'     => 'App\Models\UserType',
                'entity'    => 'userType',
                'attribute' => 'name',
                'type'      => 'select',
            ]);
            $this->xPanel->addColumn([
                'name'          => "active",
                'label'         => __t("Active"),
                'type'          => 'model_function',
                'function_name' => 'getActiveHtml',
            ]);

            // FIELDS
            $this->xPanel->addField([
                'name'       => 'email',
                'label'      => __t("Email"),
                'type'       => 'email',
                'attributes' => [
                    'placeholder' => __t("Email"),
                ],
            ]);
            $this->xPanel->addField([
                'name'       => 'password',
                'label'      => __t("Password"),
                'type'       => 'password',
                'attributes' => [
                    'placeholder' => __t("Password"),
                ],
            ], 'create');
            $this->xPanel->addField([
                'label'       => __t("Gender"),
                'name'        => 'gender_id',
                'type'        => 'select_from_array',
                'options'     => $this->gender(),
                'allows_null' => false,
            ]);
            $this->xPanel->addField([
                'name'       => 'name',
                'label'      => __t("Name"),
                'type'       => 'text',
                'attributes' => [
                    'placeholder' => __t("Name"),
                ],
            ]);
            $this->xPanel->addField([
                'name'       => 'about',
                'label'      => __t("About"),
                'type'       => 'textarea',
                'attributes' => [
                    'placeholder' => __t("About the user"),
                    'rows' => 5,
                ],
            ]);
            $this->xPanel->addField([
                'name'       => 'phone',
                'label'      => __t("Phone"),
                'type'       => 'text',
                'attributes' => [
                    'placeholder' => __t("Phone"),
                ],
            ]);
            $this->xPanel->addField([
                'name'  => 'phone_hidden',
                'label' => __t("Phone hidden"),
                'type'  => 'checkbox',
            ]);
            $this->xPanel->addField([
                'label'     => __t("Country"),
                'name'      => 'country_code',
                'model'     => 'App\Models\Country',
                'entity'    => 'country',
                'attribute' => 'asciiname',
                'type'      => 'select2',
            ]);
            $this->xPanel->addField([
                'name'      => 'user_type_id',
                'label'     => __t("Type"),
                'model'     => 'App\Models\UserType',
                'entity'    => 'userType',
                'attribute' => 'name',
                'type'      => 'select2',
            ]);
            $this->xPanel->addField([
                'name'  => 'is_admin',
                'label' => __t("Is admin"),
                'type'  => 'checkbox',
            ]);
            $this->xPanel->addField([
                'name'  => 'active',
                'label' => __t("Active"),
                'type'  => 'checkbox',
            ]);
            $this->xPanel->addField([
                'name'  => 'blocked',
                'label' => __t("Blocked"),
                'type'  => 'checkbox',
            ]);
        }

        // Encrypt password
        if (Input::has('password')) {
            Input::merge(['password' => bcrypt(Input::get('password'))]);
        }
    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud();
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud();
    }

    public function account()
    {
        // FIELDS
        $this->xPanel->addField([
            'label'       => __t("Gender"),
            'name'        => 'gender_id',
            'type'        => 'select_from_array',
            'options'     => $this->gender(),
            'allows_null' => false,
        ]);
        $this->xPanel->addField([
            'name'        => 'name',
            'label'       => __t("Name"),
            'type'        => 'text',
            'placeholder' => __t("Name"),
        ]);
        $this->xPanel->addField([
            'name'        => 'about',
            'label'       => __t("About"),
            'type'        => 'textarea',
            'placeholder' => __t("About the user"),
        ]);
        $this->xPanel->addField([
            'name'        => 'email',
            'label'       => __t("Email"),
            'type'        => 'email',
            'placeholder' => __t("Email"),
        ]);
        $this->xPanel->addField([
            'name'        => 'password',
            'label'       => __t("Password"),
            'type'        => 'password',
            'placeholder' => __t("Password"),
        ]);
        $this->xPanel->addField([
            'name'        => 'phone',
            'label'       => __t("Phone"),
            'type'        => 'text',
            'placeholder' => __t("Phone"),
        ]);
        $this->xPanel->addField([
            'name'  => 'phone_hidden',
            'label' => "Phone hidden",
            'type'  => 'checkbox',
        ]);
        $this->xPanel->addField([
            'label'     => __t("Country"),
            'name'      => 'country_code',
            'model'     => 'App\Models\Country',
            'entity'    => 'country',
            'attribute' => 'asciiname',
            'type'      => 'select2',
        ]);
        $this->xPanel->addField([
            'name'      => 'user_type_id',
            'label'     => __t("Type"),
            'model'     => 'App\Models\UserType',
            'entity'    => 'userType',
            'attribute' => 'name',
            'type'      => 'select2',
        ]);

        // Get logged user
        if (Auth::check()) {
            return $this->edit(Auth::user()->id);
        } else {
            abort(403, 'Not allowed.');
        }
    }

    public function gender()
    {
        $entries = Gender::where('translation_lang', config('app.locale'))->get();
        if (is_null($entries)) {
            return [];
        }

        $tab = [];
        foreach ($entries as $entry) {
            $translationOf = (!empty($entry->translation_of)) ? $entry->translation_of : $entry->id;
            if ($entry->translation_lang == config('applang.abbr')) {
                $translationOf = $entry->id;
            }
            $tab[$translationOf] = $entry->name;
        }

        return $tab;
    }
}
