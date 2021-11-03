<div class="panel-heading">
    <h4 class="panel-title">
        <a data-toggle="collapse" href=".pmpro-collapse-orders">
            Paid Memberships Pro
            <b class="caret"></b>
        </a>
    </h4>
</div>
<div class="pmpro-collapse-orders panel-collapse collapse in">
    <div class="panel-body">
        <div class="sidebar-block-header2"><strong>Paid Memberships Pro</strong> (<a data-toggle="collapse" href=".pmpro-collapse-orders">{{ __('close') }}</a>)</div>
       	<!-- <div id="pmpro-loader">
        	<img src="{{ asset('img/loader-tiny.gif') }}" />
        </div> -->
        @if( $results )
        <ul class="sidebar-block-list pmpro-orders-list">
            <li><strong>Username:</strong> <a href="{{$url}}forums/users/{{$results->user_login}}" target="_blank">{{$results->user_login}}</a></li>
            <li><strong>Level:</strong> {{$results->level}} (
            @if( $results->order_id != 0 )
            <a href="{{$url}}wp-admin/admin.php?page=pmpro-orders&order={{$results->order_id}}" target="_blank">{{$results->order_total}}</a>
            @else
            {{ __("N/A") }}
            @endif
            )
            </li>
            <li><strong>Key:</strong> {{$results->license}} </li>
            <li><strong>Refunded:</strong> @if( $results->refunds_last_order_id )
            <span style="color:red;">{{ __("Yes") }}</span>
            ( <a href="{{$url}}wp-admin/admin.php?page=pmpro-orders&order={{$results->refunds_last_order_id}}" target="_blank">#{{$results->refunds_last_order_id}}</a> )
            @else
            {{ __("No") }}
            @endif
            </li>
            <li><strong>Num. Refunds:</strong> {{$results->refunds_total_found}}</li>
            <li><strong>User Notes:</strong> {{$results->pmpro_user_notes}}</li>
        </ul>
        @elseif( $error )
        <div class="text-help margin-top-10 edd-no-orders">{{ $error }}</div>
        @else
        <div class="text-help margin-top-10 edd-no-orders">{{ __("No data found") }}</div>
        @endif

        @if( $results )
            <div class="margin-top-10 small pmpro-actions">
                <a href="#" class="sidebar-block-link pmpro-refresh"><i class="glyphicon glyphicon-refresh"></i> {{ __("Refresh") }}</a>
                | <a href="{{ $url }}wp-admin/user-edit.php?user_id={{ $results->user_id }}" class="sidebar-block-link" target="_blank">View WP User</a> |
                <a href="{{ $url }}wp-admin/admin.php?page=pmpro-orders&filter=all&&s={{ $customer_email }}" class="sidebar-block-link" target="_blank">View All Orders</a>
            </div>
        @endif
        
	   
    </div>
</div>
