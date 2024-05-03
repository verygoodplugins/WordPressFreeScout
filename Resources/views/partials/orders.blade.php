
<div class="conv-sidebar-block">
	<div class="panel-group accordion">
		<div class="panel panel-default" id="wordpress-freescout">
			<div class="panel-heading">
				<h4 class="panel-title">
					<a data-toggle="collapse" href=".wordpress-collapse-orders">
						WordPress
						<b class="caret"></b>
					</a>
				</h4>
			</div>
			<div class="wordpress-collapse-orders panel-collapse collapse in">
				<div class="panel-body">
					<div id="wordpress-loader">
						<img src="{{ asset('img/loader-tiny.gif') }}" />
					</div>
					@if( $results )
						<ul class="sidebar-block-list wordpress-orders-list">
							<li><i class="glyphicon glyphicon-user"></i><a href="{{$results->edit_url}}" target="_blank">{{$results->first_name}} {{$results->last_name}}</a></li>
							<li><label>Registered</label>{{ $results->registered }}</li>
							<li><label>Actve CRM</label>{{ $results->active_crm }}</li>
							<li><label>Last License Check</label>{{ $results->last_license_check }}</li>
							<li><label>Version</label>

								@if( $results->version == $results->current_version )
									<span class="label label-success">{{ $results->version }}</span>
								@else
									<span class="label label-danger">{{ $results->version }}</span>
								@endif
							</li>
						</ul>

						<h5><i class="glyphicon glyphicon-cog"></i> Active Integrations</h5>

						<ul class="label-cloud">
							@foreach( $results->integrations as $integration )
								<li><span class="label label-primary">{{ strtoupper(str_replace('-', ' ', $integration ) ) }}</span></li>
							@endforeach
						</ul>

						@if ( isset($results->crm_name) )
							<h5><i class="glyphicon glyphicon-tag"></i> {{ $results->crm_name }} Tags</h5>

							<ul class="label-cloud">
								@foreach( $results->tags as $tag )
									<li><span class="label label-info">{{ strtoupper( $tag ) }}</span></li>
								@endforeach
							</ul>

							<a href="{{ $results->crm_edit_url }}" target="_blank" class="btn btn-trans btn-xs">View in {{ $results->crm_name }} &rarr;</a>

						@endif

						<h5><i class="glyphicon glyphicon-shopping-cart"></i> EDD Orders</h5>

						@if( $results->edd_orders )
							<ul class="sidebar-block-list edd-orders-list list-group">
								@foreach( $results->edd_orders as $order )
									<li class="list-group-item">
										@if ( $order->is_refunded )
											<span class="label label-danger"><i class="glyphicon glyphicon-remove-circle"></i> Refunded</span>
										@elseif ( $order->is_renewal )
											<span class="label label-primary"><i class="glyphicon glyphicon-refresh"></i> Renewal</span>
										@elseif ( $order->status == 'complete' )
											<span class="label label-success"><i class="glyphicon glyphicon-ok-circle"></i> Complete</span>
										@else
											<span class="label label-default">{{ ucfirst( $order->status ) }}</span>
										@endif

										<a href="{{ $order->edit_order_url }}" target="_blank">#{{ $order->ID }}</a> -

										{{ $order->purchase_amount }}

										<ul class="edd-order-items-list">

											@foreach( $order->products as $item )
												<li>
													{{ $item->name }} -
													{{ $item->price }}
												</li>
											@endforeach

										</ul>

										<div class="edd-order-meta">
											{{ $order->purchase_date }} - {{ $order->payment_method }}
										</div>

									</li>
								@endforeach
							</ul>
						@else
							<div class="text-help margin-top-10 edd-no-orders">{{ __("No orders found") }}</div>
						@endif

                        <h5><i class="glyphicon glyphicon-shopping-cart"></i> EDD Subscriptions</h5>

                        @if( isset($results->edd_subscriptions) )
                        <ul class="sidebar-block-list edd-orders-list list-group">
                            @foreach( $results->edd_subscriptions as $subscription )
                                <li class="list-group-item">
                                    @switch($subscription->status)
                                        @case('Active')
                                            <span class="label label-primary"> Active</span>
                                        @break
                                        @case('Pending')
                                            <span class="label label-info"> Pending</span>
                                        @break
                                        @case('Expired')
                                            <span class="label label-danger"> Expired</span>
                                        @break
                                        @case('Completed')
                                            <span class="label label-success"> Completed</span>
                                        @break
                                        @default
                                        <span class="label label-default">{{ $subscription->status ?? '' }}</span>
                                    @endswitch

                                    <a href="{{ $subscription->detail_link }}" target="_blank">{{ $subscription->subscription_id ?? '' }}</a>

                                    {{-- {{ $order->purchase_amount }} --}}

                                    <ul class="edd-order-items-list">
                                        <li>{{ $subscription->product_name }} - <span>{{ $subscription->initial_amount }} then {{ $subscription->billing_frequency }}</span> </li>
                                    </ul>

                                    <div class="edd-order-meta">
                                        <div><strong>Created:</strong> {{ $subscription->creation_date ?? '' }}</div>
                                        <div><strong>Expiration:</strong> {{ $subscription->expiration_date ?? '' }}</div>
                                        <div><strong>Times Billed:</strong> {{ $subscription->times_billed ?? '' }}</div>
                                    </div>

                                </li>
                            @endforeach
                        </ul>
						@else
							<div class="text-help margin-top-10 edd-no-subscriptions">{{ __("No subscriptions found") }}</div>
						@endif

						<h5><i class="glyphicon glyphicon-credit-card"></i> EDD Licenses</h5>

						@if( $results->licenses )
							<ul class="sidebar-block-list edd-orders-list list-group">
								@foreach( $results->licenses as $license )
									<li class="list-group-item">
										@if ( $license->is_active )
											<span class="label label-success"><i class="glyphicon glyphicon-ok-circle"></i> Active</span>
										@else
											<span class="label label-warning"><i class="glyphicon glyphicon-time"></i> Expired</span>
										@endif

										<a href="{{ $license->edit_url }}" target="_blank">#{{ $license->ID }}</a>

										<code>{{ $license->license_key }}</code>

										<ul class="edd-order-items-list">

											@foreach( $license->sites as $site )
												<li><a href="https://{{ $site }}" target="_blank">{{ $site }}</a></li>
											@endforeach

										</ul>

										<div class="edd-order-meta">
											@if ( $license->expires )
												Expires {{ $license->expires }}
											@else
												Lifetime license
											@endif
										</div>

									</li>
								@endforeach
							</ul>
						@else
							<div class="text-help margin-top-10 edd-no-orders">{{ __("No licenses found") }}</div>
						@endif

					@elseif( $error )
						<div class="text-help margin-top-10 edd-no-orders">{{ $error }}</div>
					@else
						<div class="text-help margin-top-10 edd-no-orders">{{ __("No data found") }}</div>
					@endif


				</div>

			</div>
		</div>
	</div>
</div>
