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

namespace App\Http\Requests\Admin;

class AdRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id'         => 'required|not_in:0',
            'ad_type_id'          => 'required|not_in:0',
            'company_name'        => 'required|mb_between:10,200|whitelist_word_title',
            'company_description' => 'required|mb_between:10,3000|whitelist_word',
            'title'               => 'required|between:5,200',
            'description'         => 'required|between:5,3000',
            'contact_name'        => 'required|between:3,200',
            'contact_email'       => 'required|email|max:100',
        ];
    }
}
