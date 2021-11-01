<form class="form-horizontal margin-top margin-bottom" method="POST" action="">
    {{ csrf_field() }}

    <div class="form-group{{ $errors->has('settings.pmpro->url') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label">{{ __('Site URL') }}</label>

        <div class="col-sm-6">
            <div class="input-group input-sized-lg">
                <span class="input-group-addon input-group-addon-grey">https://</span>
                <input type="text" class="form-control input-sized-lg" name="settings[pmpro.url]" value="{{ old('settings') ? old('settings')['pmpro.url'] : $settings['pmpro.url'] }}">
            </div>

            <p class="form-help">
                {{ __('Example') }}: www.paidmembershipspro.com
            </p>
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">{{ __('Username') }}</label>

        <div class="col-sm-6">
            <input type="text" class="form-control input-sized-lg" name="settings[pmpro.username]" value="{{ $settings['pmpro.username'] }}">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">{{ __('Application Password') }}</label>

        <div class="col-sm-6">
            <input type="password" class="form-control input-sized-lg" name="settings[pmpro.password]" value="{{ $settings['pmpro.password'] }}">

            <p class="form-help">
                {{ __("You can generate an Application password by following this guide - ") }}<br/>
                <a href="#">Creating an Application Password</a>
            </p>
        </div>
    </div>

    <div class="form-group margin-top margin-bottom">
        <div class="col-sm-6 col-sm-offset-2">
            <button type="submit" class="btn btn-primary">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</form>