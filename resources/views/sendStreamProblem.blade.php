@component('mail::message')

@component('mail::panel')
    Jsou problémy s kanálem {{$streamName}} !
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'success'])
Proklik na kanál
@endcomponent

@endcomponent
