@component('mail::message')
    <p>
        Přístupy do systému jsou: {{$email}} {{$password}}
    </p>
    <a href="{{$url}}">Adresa do systemu</a>
@endcomponent
