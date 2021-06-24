@component('mail::message')

<p>
    Přístup do systému:
</p>
@component('mail::panel')
    {{$email}} / {{$password}}
@endcomponent

@component('mail::button', ['url' => $url, 'color' => 'success'])
    
@endcomponent
    Vstup do systému
@endcomponent
