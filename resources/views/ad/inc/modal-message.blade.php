<div class="modal fade" id="applyJob" tabindex="-1" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">{{ t('Close') }}</span>
				</button>
				<h4 class="modal-title"><i class=" icon-mail-2"></i> {{ t('Contact Employer') }} </h4>
			</div>
			<form role="form" method="POST" action="{{ lurl($ad->id . '/contact') }}" enctype="multipart/form-data">
				{!! csrf_field() !!}
				<div class="modal-body">

					@if (count($errors) > 0 and old('msg_form')=='1')
						<div class="alert alert-danger">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
							<ul class="list list-check">
								@foreach($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					@if (Auth::check())
						<input type="hidden" name="sender_name" value="{{ $user->name }}">
						<input type="hidden" name="sender_email" value="{{ $user->email }}">
						<input type="hidden" name="sender_phone" value="{{ $user->phone }}">
					@else
						<div class="form-group required <?php echo ($errors->has('sender_name')) ? 'has-error' : ''; ?>">
							<label for="sender_name" class="control-label">{{ t('Name') }} <sup>*</sup></label>
							<input id="sender_name" name="sender_name" class="form-control" placeholder="{{ t('Your name') }}" type="text"
								   value="{{ old('sender_name') }}">
						</div>

						<div class="form-group required <?php echo ($errors->has('sender_email')) ? 'has-error' : ''; ?>">
							<label for="sender_email" class="control-label">{{ t('E-mail') }} <sup>*</sup></label>
							<div class="input-group">
								<span class="input-group-addon"><i class="icon-mail"></i></span>
								<input id="sender_email" name="sender_email" type="text" placeholder="{{ t('i.e. you@gmail.com') }}"
									   class="form-control" value="{{ old('sender_email') }}">
							</div>
						</div>

						<div class="form-group required <?php echo ($errors->has('sender_phone')) ? 'has-error' : ''; ?>">
							<label for="sender_phone" class="control-label">{{ t('Phone Number') }} <sup>*</sup></label>
							<div class="input-group">
								<span class="input-group-addon"><i class="icon-phone-1"></i></span>
								<input id="sender_phone" name="sender_phone" type="text" maxlength="60" class="form-control"
									   value="{{ old('sender_phone') }}">
							</div>
						</div>
					@endif

					<!-- Message -->
					<div class="form-group required <?php echo ($errors->has('message')) ? 'has-error' : ''; ?>">
						<label for="message" class="control-label">{{ t('Message') }} <span class="text-count">(500 max) </span><sup>*</sup></label>
						<textarea id="message" name="message" class="form-control required" placeholder="{{ t('Your message here...') }}"
								  rows="5">{{ old('message') }}</textarea>
					</div>

					<!-- Resume -->
					<div class="form-group required <?php echo ($errors->has('filename')) ? 'has-error' : ''; ?>">
						<label for="filename" class="control-label">{{ t('Resume') }} </label>
						<input id="filename" name="filename" type="file" class="file">
						<p class="help-block">{{ t('File types: :file_types', ['file_types' => showValidFileTypes('file')]) }}</p>
						@if (!empty($resume) and file_exists(public_path($resume->filename)))
							<div>
								<a class="btn btn-default" href="{{ url($resume->filename) }}" target="_blank">
									<i class="icon-attach-2"></i> {{ t('Donwload current Resume') }}
								</a>
							</div>
						@endif
					</div>

					<!-- Captcha -->
					@if (config('settings.activation_recaptcha'))
						<div class="form-group required <?php echo ($errors->has('g-recaptcha-response')) ? 'has-error' : ''; ?>">
							<label class="control-label" for="g-recaptcha-response">{{ t('We do not like robots') }}</label>
							<div>
								{!! Recaptcha::render(['lang' => $lang->get('abbr')]) !!}
							</div>
						</div>
					@endif

					<input type="hidden" name="ad" value="{{ $ad->id }}">
					<input type="hidden" name="msg_form" value="1">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">{{ t('Cancel') }}</button>
					<button type="submit" class="btn btn-success pull-right">{{ t('Send message') }}</button>
				</div>
			</form>
		</div>
	</div>
</div>
@section('after_scripts')
	@parent
    
	<script src="{{ url('/assets/js/fileinput.min.js') }}" type="text/javascript"></script>
	@if (file_exists(public_path() . '/assets/js/fileinput_locale_'.$lang->get('abbr').'.js'))
		<script src="{{ url('/assets/js/fileinput_locale_'.$lang->get('abbr').'.js') }}" type="text/javascript"></script>
	@endif
    
	<script language="javascript">
		/* initialize with defaults (resume) */
		$('#filename').fileinput(
		{
			'showPreview': false,
			'allowedFileExtensions': {!! getUploadFileTypes('file', true) !!},
			'browseLabel': '{!! t("Browse") !!}',
			'showUpload': false,
			'showRemove': false,
			'maxFileSize': {{ (int)config('settings.upload_max_file_size', 1000) }}
		});
	</script>
@endsection