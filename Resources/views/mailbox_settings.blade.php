@extends('layouts.app')

@section('title_full', 'PMPro'.' - '.$mailbox->name)

@section('sidebar')
    @include('partials/sidebar_menu_toggle')
    @include('mailboxes/sidebar_menu')
@endsection

@section('content')

    <div class="section-heading">
        Paid Memberships Pro - Freescout Application
    </div>

    <!-- @include('partials/flash_messages') -->
    
 	<div class="row-container">
        <div class="row">
            <div class="col-xs-12">
                @include('pmprofreescout::settings')
            </div>
        </div>
    </div>
  
@endsection