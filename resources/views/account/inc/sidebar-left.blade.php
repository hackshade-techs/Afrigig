<aside>
	<div class="inner-box">
		<div class="user-panel-sidebar">
            
            @if (isset($user))
                <div class="collapse-box">
                    <h5 class="collapse-title no-border">
                        {{ t('My Account') }}&nbsp;
                        <a href="#MyClassified" data-toggle="collapse" class="pull-right"><i class="fa fa-angle-down"></i></a>
                    </h5>
                    <div class="panel-collapse collapse in" id="MyClassified">
                        <ul class="acc-list">
                            <li>
                                <a{!! ($pagePath=='') ? ' class="active"' : '' !!} href="{{ lurl('account') }}">
                                    <i class="icon-home"></i> {{ t('Personal Home') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- /.collapse-box  -->
                
                @if (!empty($user->user_type_id) and $user->user_type_id != 0)
                    <div class="collapse-box">
                        <h5 class="collapse-title">
                            {{ t('My Ads') }}&nbsp;
                            <a href="#MyAds" data-toggle="collapse" class="pull-right"><i class="fa fa-angle-down"></i></a>
                        </h5>
                        <div class="panel-collapse collapse in" id="MyAds">
                            <ul class="acc-list">
                                @if (in_array($user->user_type_id, [1, 2]))
                                    <li>
                                        <a{!! ($pagePath=='myads') ? ' class="active"' : '' !!} href="{{ lurl('account/myads') }}">
                                        <i class="icon-docs"></i> {{ t('My ads') }}&nbsp;
                                        <span class="badge">{{ isset($count_my_ads) ? $count_my_ads : 0 }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a{!! ($pagePath=='pending-approval') ? ' class="active"' : '' !!} href="{{ lurl('account/pending-approval') }}">
                                        <i class="icon-hourglass"></i> {{ t('Pending approval') }}&nbsp;
                                        <span class="badge">{{ isset($count_pending_ads) ? $count_pending_ads : 0 }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a{!! ($pagePath=='archived') ? ' class="active"' : '' !!} href="{{ lurl('account/archived') }}">
                                        <i class="icon-folder-close"></i> {{ t('Archived ads') }}&nbsp;
                                        <span class="badge">{{ isset($count_archived_ads) ? $count_archived_ads : 0 }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if (in_array($user->user_type_id, [1, 3]))
                                    <li>
                                        <a{!! ($pagePath=='favourite') ? ' class="active"' : '' !!} href="{{ lurl('account/favourite') }}">
                                        <i class="icon-heart"></i> {{ t('Favourite ads') }}&nbsp;
                                        <span class="badge">{{ isset($count_favourite_ads) ? $count_favourite_ads : 0 }}</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a{!! ($pagePath=='saved-search') ? ' class="active"' : '' !!} href="{{ lurl('account/saved-search') }}">
                                        <i class="icon-star-circled"></i> {{ t('Saved search') }}&nbsp;
                                        <span class="badge">{{ isset($count_saved_search) ? $count_saved_search : 0 }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <!-- /.collapse-box  -->
                
                    <div class="collapse-box">
                        <h5 class="collapse-title">
                            {{ t('Terminate Account') }}&nbsp;
                            <a href="#TerminateAccount" data-toggle="collapse" class="pull-right"><i class="fa fa-angle-down"></i></a>
                        </h5>
                        <div class="panel-collapse collapse in" id="TerminateAccount">
                            <ul class="acc-list">
                                <li>
                                    <a{!! ($pagePath=='close') ? ' class="active"' : '' !!} href="{{ lurl('account/close') }}">
                                    <i class="icon-cancel-circled "></i> {{ t('Close account') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- /.collapse-box  -->
                @endif
            @endif

		</div>
	</div>
	<!-- /.inner-box  -->
</aside>