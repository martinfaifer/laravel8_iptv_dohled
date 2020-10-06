IPTV dohled verze 4.x.x -> inprogress


Hlavní diagnostika -> TSDuck -> analytický nástroj pro zpracování streamů ( obsahuje bitrate, volume, dekryptaci kanálu || funkčnost kanálu )
Tvorba náhledu -> FFMpeg -> název / ID kanálu .jpg , který se aktualizuje cca 1x 1s

Pokud selze TSDuck pokusí se kanál zkontrolovat ffprobe, která je několikrát nárocnejsi na cpu usage => zahozeni do queue

Redis db pouzito jako cache pro MySQL, které slouzi pro rychlejsi odbavení pozadavků
Redis db slouzí také pro queue, které je xkrát rychlejsi nez MySQL

composer require predis/predis
pro spustení redis config/database.php  => 'client' => env('REDIS_CLIENT', 'predis'),

Redis -> queue
Redis -> cache pro mysql

.env
    CACHE_DRIVER=redis

