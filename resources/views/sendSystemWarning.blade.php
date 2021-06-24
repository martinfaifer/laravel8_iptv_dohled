@component('mail::message')
    @if ($partOfSystem == "cpu")
        Minimálně jedno jádro je vytíženo na 100%!
    @endif
    @if ($partOfSystem == "ram")
        Paměť Ram je využitá na více jak 80%!
    @endif
    @if ($partOfSystem == "swap")
        System začal swapovat!
    @endif
    @if ($partOfSystem == "hdd")
        Dochází místo na disku, nyní využito více jak 80%!
    @endif

    @if ($partOfSystem == "ssl")
        Za méně jak jeden den, vypší ssl certifikát!!!
    @endif


@endcomponent
