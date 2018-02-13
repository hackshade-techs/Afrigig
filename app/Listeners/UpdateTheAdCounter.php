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

namespace App\Listeners;

use App\Events\AdWasVisited;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class UpdateTheAdCounter
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }
    
    /**
     * Handle the event.
     *
     * @param  AdWasVisited $event
     * @return bool|void
     */
    public function handle(AdWasVisited $event)
    {
        // Don't count the self-visits
        if (Auth::check()) {
            if (Auth::user()->id == $event->ad->user_id) {
                return false;
            }
        }

		if (!session()->has('adIsVisited')) {
			return $this->updateCounter($event->ad);
		} else {
			if (session()->get('adIsVisited') != $event->ad->id) {
				return $this->updateCounter($event->ad);
			} else {
				return false;
			}
		}
    }

	/**
	 * @param $ad
	 */
	public function updateCounter($ad)
	{
		$ad->visits = $ad->visits + 1;
		$ad->save();
		session()->put('adIsVisited', $ad->id);
	}
}
