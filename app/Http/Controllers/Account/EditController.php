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

namespace App\Http\Controllers\Account;

use App\Models\Resume;
use App\Models\User;
use App\Http\Controllers\FrontController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EditController extends AccountBaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function details(Request $request)
    {
        // Validation
        // Check if email has changed
        $email_changed = ($request->input('email') != $this->user->email);

        if (empty($this->user->user_type_id) or $this->user->user_type_id == 0) {
            $rules = [
                'user_type' => 'required|not_in:0',
            ];
        } else {
            $rules = [
                'gender' => 'required|not_in:0',
                'name'   => 'required|max:100',
                'phone'  => 'required|max:60',
                'email'  => ($email_changed) ? 'required|email|unique:users,email' : 'required|email',
            ];
        }

        // Check 'resume' is required
        $resume = null;
        if ($request->hasFile('filename')) {
            $resume = Resume::where('user_id', $this->user->id)->first();
            $rules['filename'] = 'required|mimes:' . getUploadFileTypes('file') . '|max:' . (int)config('settings.upload_max_file_size', 1000);
        }
        $this->validate($request, $rules);


        // UPDATE
        $user = User::find($this->user->id);
        if (empty($this->user->user_type_id) or $this->user->user_type_id == 0) {
            $user->user_type_id = $request->input('user_type');
        } else {
            $user->gender_id = $request->input('gender');
            $user->name = $request->input('name');
            $user->country_code = $request->input('country');
            $user->phone = $request->input('phone');
            $user->phone_hidden = $request->input('phone_hidden');
            if ($email_changed) {
                $user->email = $request->input('email');
            }
            $user->receive_newsletter = $request->input('receive_newsletter');
            $user->receive_advice = $request->input('receive_advice');
        }
        $user->save();


        // Save Resume
        if ($request->hasFile('filename')) {
            // Create resume if doesn't exists
            if (empty($resume)) {
                $resumeInfo = [
                    'country_code' => $this->country->get('code'),
                    'user_id'      => $this->user->id,
                    'active'       => 1,
                ];
                $resume = new Resume($resumeInfo);
                $resume->save();
            }

            $resume->filename = $request->file('filename');
            $resume->save();
        }

        flash()->success(t("Your details account has update successfully."));

        return redirect($this->lang->get('abbr') . '/account');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function settings(Request $request)
    {
        // Validation
        $this->validate($request, [
            'password' => 'between:5,15|confirmed',
        ]);

        // Update
        $user = User::find($this->user->id);
        $user->disable_comments = (int)$request->input('disable_comments');
        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
        }
        $user->save();

        flash()->success(t("Your settings account has update successfully."));

        return redirect($this->lang->get('abbr') . '/account');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function preferences()
    {
        $data = [];

        return view('account.home', $data);
    }
}
