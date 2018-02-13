<?php
/**
 * LaraClassified - Geo Classified Ads Software
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

/**
 * Default Admin translator (e.g. admin::messages.php)
 *
 * @param $string
 * @param array $params
 * @param string $file
 * @param null $locale
 * @return string|\Symfony\Component\Translation\TranslatorInterface
 */
function __t($string, $params = [], $file = 'admin::messages', $locale = null)
{
    if (is_null($locale)) {
        $locale = config('app.locale');
    }

    return trans($file . '.' . $string, $params, null, $locale);
}

/**
 * Checkbox Display
 *
 * @param $fieldValue
 * @return string
 */
function checkboxDisplay($fieldValue)
{
    if ($fieldValue == 1) {
        return '<i class="fa fa-check-square-o" aria-hidden="true"></i>';
    } else {
        return '<i class="fa fa-square-o" aria-hidden="true"></i>';
    }
}

/**
 * Ajax Checkbox Display
 *
 * @param $id
 * @param $table
 * @param $field
 * @param null $fieldValue
 * @return string
 */
function ajaxCheckboxDisplay($id, $table, $field, $fieldValue = null)
{
    $lineId = $field.$id;
    $lineId = str_replace('.', '', $lineId); // fix JS bug (in admin layout)
    $data = 'data-table="' . $table . '" 
			data-field="'.$field.'" 
			data-line-id="' . $lineId . '" 
			data-id="' . $id . '" 
			data-value="' . (isset($fieldValue) ? $fieldValue : 0) . '"';

    // Decoration
    if (isset($fieldValue) && $fieldValue == 1) {
        $html = '<i id="' . $lineId . '" class="fa fa-check-square-o" aria-hidden="true"></i>';
    } else {
        $html = '<i id="' . $lineId . '" class="fa fa-square-o" aria-hidden="true"></i>';
    }
    $html = '<a href="" class="ajax-request" ' . $data . '>' . $html . '</a>';

    return $html;
}

/**
 * Advanced Ajax Checkbox Display
 *
 * @param $id
 * @param $table
 * @param $field
 * @param null $fieldValue
 * @return string
 */
function installAjaxCheckboxDisplay($id, $table, $field, $fieldValue = null)
{
    $lineId = $field.$id;
    $lineId = str_replace('.', '', $lineId); // fix JS bug (in admin layout)
    $data = 'data-table="' . $table . '" 
			data-field="'.$field.'" 
			data-line-id="' . $lineId . '" 
			data-id="' . $id . '" 
			data-value="' . $fieldValue . '"';

    // Decoration
    if ($fieldValue == 1) {
        $html = '<i id="' . $lineId . '" class="fa fa-check-square-o" aria-hidden="true"></i>';
    } else {
        $html = '<i id="' . $lineId . '" class="fa fa-square-o" aria-hidden="true"></i>';
    }
    $html = '<a href="" class="ajax-request" ' . $data . '>' . $html . '</a>';

    // Install country's decoration
    $html .= ' - ';
    if ($fieldValue == 1) {
        $html .= '<a href="" id="install' . $id . '" class="ajax-request btn btn-xs btn-success" ' . $data . '><i class="fa fa-download"></i> ' . __t('Installed') . '</a>';
    } else {
        $html .= '<a href="" id="install' . $id . '" class="ajax-request btn btn-xs btn-default" ' . $data . '><i class="fa fa-download"></i> ' . __t('Install') . '</a>';
    }

    return $html;
}

/**
 * Generate the Ad's link from the Admin panel
 *
 * @param $ad
 * @return string
 */
function getAdLink($ad)
{
    $out = '';

    // Get payment Info
    $payment = \App\Models\Payment::where('ad_id', $ad->id)->orderBy('id', 'DESC')->first();
    if (!empty($payment)) {
        // Get Pack Info
        $package = \App\Models\Package::transById($payment->package_id);
        if (!empty($package)) {
            if ($ad->featured == 1) {
                $class = 'text-success';
                $info = '';
            } else {
                $class = 'text-danger';
                $info = ' (' . __t('Expired') . ')';
            }
            $out = ' <i class="fa fa-check-circle ' . $class . ' tooltipHere"
                    title="" data-placement="bottom" data-toggle="tooltip"
                    type="button" data-original-title="' . $package->short_name . $info . '">
                </i>';
        }
    }

    $out = '<a href="/' . config('app.locale') . '/' . slugify($ad->title) . '/' . $ad->id . '.html" target="_blank">' . str_limit($ad->title, 60) . '</a>' . $out;

    return $out;
}