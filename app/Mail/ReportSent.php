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

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Helpers\Arr;
use App\Models\Ad;

class ReportSent extends Mailable
{
    use Queueable, SerializesModels;

    public $ad;
    public $report;

    /**
     * Create a new message instance.
     *
     * @param Ad $ad
     * @param $report
     * @param $recipient
     */
    public function __construct(Ad $ad, $report, $recipient)
    {
        $this->ad = $ad;
        $this->report = (is_array($report)) ? Arr::toObject($report) : $report;

		$this->to($recipient->email, $recipient->name);
		$this->replyTo($this->report->email, $this->report->email);
        $this->subject(trans('mail.ad_report_sent_title', [
            'app_name'      => config('settings.app_name'),
            'country_code'  => $ad->country_code
        ]));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.ad.report-sent');
    }
}
