@component('mail::message')

@component('mail::panel')
    Nefunguje kanál {{$stream}} !
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'success'])
Proklik na kanál
@endcomponent

@endcomponent
