@component('mail::message')
# Congratulations {{ $user->first_name }} you have been granted access to Shyft Portal

@component('mail::button', ['url' => Config::get('shyft.url').'/auth/password/set/'.$user->remember_token, 'color' => 'red'])
Complete Registration
@endcomponent

Thanks,
@endcomponent
