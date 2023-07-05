@component('mail::message')
    {{ __('Your server \':server\' has been provisioned and is ready to use.', ['server' => $server->name]) }}

    {{ __('You can access your server by clicking the button below.') }}

    @component('mail::button', ['url' => route('servers.show', $server)])
        {{ __('View Server') }}
    @endcomponent

    {{ __('Instead of accessing your server through the web interface, you can also connect to it using the following command:') }}

    @component('mail::panel')
        `ssh {{ $server->username.'@'.$server->public_ipv4 }}`
    @endcomponent

    {{ __('Below you will find the details of your server.') }}

    @component('mail::panel')
        {{ __('Username') }}: `{{ $server->username }}`
        <br />
        {{ __('Password') }}: `{{ $server->password }}`
        <br />
        {{ __('IP Address') }}: `{{ $server->public_ipv4 }}`
        <br />
        {{ __('Database Username') }}: `{{ config('eddy.server_defaults.database_name') }}`
        <br />
        {{ __('Database Password') }}: `{{ $server->database_password }}`
    @endcomponent

    {{ __('Good luck with your new server!') }}
@endcomponent
