<li @if (\App\Misc\Helper::isMenuSelected('design'))class="active"@endif><a href="{{ route('mailboxes.wordpress.settings', ['id'=>$mailbox->id]) }}"><i class="glyphicon glyphicon-list-alt"></i> {{ __('WordPress') }}</a></li>