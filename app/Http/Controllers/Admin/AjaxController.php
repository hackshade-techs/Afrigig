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

use Larapen\Admin\app\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\City;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as HttpRequest;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AjaxController extends Controller
{
	/**
	 * @param $table
	 * @param $field
	 * @param HttpRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function saveAjaxRequest($table, $field, HttpRequest $request)
    {
        $primaryKey = $request->input('primaryKey');
        $status = 0;
		$result = ['table' => $table, 'field' => $field, 'primaryKey' => $primaryKey, 'status' => $status];

		// Check parameters
		if (!Auth::check() or Auth::user()->is_admin != 1) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		if (!Schema::hasTable($table)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		if (!Schema::hasColumn($table, $field)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}
		$sql = 'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "'.DB::getTablePrefix().$table.'" AND COLUMN_NAME = "'.$field.'"';
		$info = DB::select(DB::raw($sql));
		if (empty($info)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		} else {
			if (isset($info[0]) and isset($info[0]->DATA_TYPE)) {
                if ($info[0]->DATA_TYPE != 'tinyint' && $table != 'settings') {
					return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
				}
                if ($info[0]->DATA_TYPE != 'text' && $table == 'settings' && $field == 'value') {
                    return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
                }
			} else {
				return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
			}
		}


		// Get table model
        $namespace = '\\App\Models\\';
        $model = null;
        $item = null;
		switch ($table) {
			case 'ads':
                $model = 'Ad';
				break;
			case 'ad_type':
                $model = 'AdType';
				break;
			case 'advertising':
                $model = 'Advertising';
				break;
			case 'categories':
                $model = 'Category';
				break;
			case 'cities':
                $model = 'City';
				break;
			case 'countries':
                $model = 'Country';
				break;
			case 'languages':
                $model = 'Language';
				break;
			case 'packages':
                $model = 'Package';
				break;
            case 'payment_methods':
                $model = 'PaymentMethod';
                break;
            case 'pages':
                $model = 'Page';
                break;
			case 'pictures':
                $model = 'Picture';
				break;
            case 'settings':
                $model = 'Setting';
                break;
			case 'subadmin1':
                $model = 'SubAdmin1';
				break;
			case 'subadmin2':
                $model = 'SubAdmin2';
				break;
			case 'users':
                $model = 'User';
				break;
		}

        // Get table data
		if (!empty($model)) {
            $model = $namespace . $model;
            $item = $model::find($primaryKey);
        }

		// Check item
		if (empty($item)) {
			return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
		}

		// UPDATE - the tinyint field

		// Geonames country data installation
		if ($table == 'countries' and $field == 'active') {
			if (strtolower(config('settings.app_default_country')) != strtolower($item->code)) {
				$resImport = false;
				if ($item->{$field} == 0) {
					$resImport = $this->importGeonamesSql($item->code);
				} else {
					$resImport = $this->removeGeonamesDataByCountryCode($item->code);
				}

				// Save data
				if ($resImport) {
					$item->{$field} = ($item->{$field} == 0) ? 1 : 0;
					$item->save();
				}

				$isDefaultCountry = 0;
			} else {
				$isDefaultCountry = 1;
				$resImport = true;
			}
		}
		else
		{
			// Save data
			$item->{$field} = ($item->{$field} == 0) ? 1 : 0;
			$item->save();

			// Set translations
			$this->updateTranslations($model, $item, $field, $item->{$field});
		}


		// JS data
		$result = ['table' => $table, 'field' => $field, 'primaryKey' => $primaryKey, 'status' => 1, 'fieldValue' => $item->{$field}];

		if (isset($isDefaultCountry)) {
			$result['isDefaultCountry'] = $isDefaultCountry;
		}
		if (isset($resImport)) {
			$result['resImport'] = $resImport;
		}

        
        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
    }


    /**
     * Import the Geonames data for the country
     *
     * @param $countryCode
     * @return bool
     */
	private function importGeonamesSql($countryCode)
	{
		// Remove all country data
		$this->removeGeonamesDataByCountryCode($countryCode);

		// Default country SQL file
		$filename = 'database/geonames/countries/' . strtolower($countryCode) .'.sql';
		$rawFilePath = storage_path($filename);
		$filePath = storage_path('app/'.$filename);

		// Check if rawFilePath exists
		if (!file_exists($rawFilePath)) {
			return false;
		}

		// Read and replace the database tables prefix
		$file = fopen($rawFilePath, 'r') or die('Unable to open file!');
		$sql = fread($file, filesize($rawFilePath));
		fclose($file);
		$sql = str_replace('<<prefix>>', DB::getTablePrefix(), $sql);


		// Create a new SQL file
		if (file_exists($filePath)) {
			unlink($filePath);
		}
		$file = fopen($filePath, 'w') or die('Unable to open file!');
		fwrite($file, $sql);
		fclose($file);


		try {
			// Temporary variable, used to store current query
			$tmpline = '';
			// Read in entire file
			$lines = file($filePath);
			// Loop through each line
			foreach ($lines as $line) {
				// Skip it if it's a comment
				if (substr($line, 0, 2) == '--' || trim($line) == '') {
					continue;
				}

				// Add this line to the current segment
				$tmpline .= $line;
				// If it has a semicolon at the end, it's the end of the query
				if (substr(trim($line), -1, 1) == ';') {
					// Perform the query
					DB::unprepared($tmpline);
					// Reset temp variable to empty
					$tmpline = '';
				}
			}
		}
		catch (\Exception $e)
		{
			$msg = 'Error when importing required data : '. $e->getMessage();
			echo '<pre>'; print_r($msg); echo '</pre>'; exit();
		}

		// Delete the SQL file
		if (file_exists($filePath)) {
			unlink($filePath);
		}

		return true;
	}

	/**
     * Remove all the country's data
     *
	 * @param $countryCode
	 * @return bool
	 */
	private function removeGeonamesDataByCountryCode($countryCode)
	{
		$deletedRows = SubAdmin1::where('code', 'LIKE', $countryCode . '.%')->delete();
		$deletedRows = SubAdmin2::where('code', 'LIKE', $countryCode . '.%')->delete();
		$deletedRows = City::where('country_code', 'LIKE', $countryCode)->delete();

        // Delete all ads entries
        $ads = Ad::where('country_code', 'LIKE', $countryCode)->get();
        if ($ads->count() > 0) {
            foreach ($ads as $ad) {
                $ad->delete();
            }
        }

		return true;
	}

    /**
     * Update translations entries - If model has translatable fields
     *
     * @param $model
     * @param $item
     * @param $field
     * @param $value
     */
	private function updateTranslations($model, $item, $field, $value)
    {
        if (property_exists($model, 'translatable')) {
            // If the entry is a default language entry, copy-paste its translations common data
            if ($item->id == $item->translation_of) {
                // ... AND don't select the current translated entry to prevent infinite recursion
                $entries = $model::where('id', '!=', $item->id)->where('translation_of', $item->translation_of)->get();

                // Copy-Paste for all languages
                if (!empty($entries)) {
                    foreach ($entries as $entry) {
                        $entry->{$field} = $value;
                        $entry->save();
                    }
                }
            }
        }
    }
}