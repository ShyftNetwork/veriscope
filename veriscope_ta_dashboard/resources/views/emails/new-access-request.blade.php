@component('mail::message')

# A new user has requested access to the Shyft Portal.


@component('mail::table')
| Key           | Value                     |
| ------------- |:-------------------------:|
| First Name    | {{ $user->first_name }}   |
| Last Name     | {{ $user->last_name }}    |
| Email         | {{ $user->email }}        |
@endcomponent

@component('mail::button', ['url' => Config::get('backoffice.url').'/backoffice/users', 'color' => 'red'])
Grant Access
@endcomponent

Thanks,
@endcomponent
