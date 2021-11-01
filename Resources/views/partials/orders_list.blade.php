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
            <li><strong>Level:</strong> {{$results->level}} (<a href="{{$url}}wp-admin/admin.php?page=pmpro-orders&order={{$results->order_id}}" target="_blank">{{$results->order_total}}</a>)</li>
            <li><strong>Key:</strong> {{$results->license}} </li>
        </ul>
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
