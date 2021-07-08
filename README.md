IPTV Dohled 

    Popis aplikace
        - dohled téměř v reaálném čase http, multicast , unicast streamů, kdy je dohledováno
            1. funkčnost streamu
            2. datové toky v audio a video Pidu
            3. scrambled packety v Pidech
            4. synchronizace Audia / videa 
        
        - Vytváření náhledů ze streamu jednou za 3 minuty, kdy je možnost tento čas upravit v app\Console\Kernel.php
        - Vykreslování datových toků a chyb ve streamech do Grafů
        - CPU dohled s vykreslením do grafu
        - Historie streamů
        - Uživatelské role ( admin / editor / view)
        - Možnost přidat Logo společnosti
        - Povolení nebo zakázání CRONU přímo z GUI
        - notifikace pomocí emailu nebo slack
        - při napojení na IPTV Dokumentaci nebo jiný systém je možnost plnení dat do dohledu, zasílání notifikací, sheduling dohledovanosti streamů
        - notifikace přímo v GUI - v mozaice nebo pomocí alertů v jiných částech systému
        - možnost nastavení statické mozaiky, pro určité streamy ( vhodné pro nejvíce sledované streamy )
        - a spousta dalších funkcí...

    - pro analýzu se používá TSDUCK
        - instalace TSDuck pomocí jednoho ze dvou souborů přiložených v adresáři ( tsduck_3.24-2109.ubuntu20_amd64.deb nebo  tsduck_3.26-2349.raspbian10_armhf.deb , záleží na OS)
    - pro generování náhledů se používá FFMPEG 
        - instalace sudo apt-get install ffmpeg
    - jako cache se používá memcached
        - instalace sudo apt-get install memcached
        - instalace balíčků pro php sudo apt-get install -y php-memcached 
    - pro Queue se používá driver Redis
        - instalace sudo apt install redis-server
        - sudo systemctl enable redis-server
    - instalace veškerých balíčků 
        - composer install
    - povolení public storage 
        - php artisan storage:link
    - nastavení DB a dat v ní
        - php artisan migrate
        - php artisan db:seed ( default user admin@admin.cz / admin)
    - nastavení CRON
        - nano /etc/crontab
            -> * *     * * *   root    cd path to app && php artisan schedule:run >> /dev/null 2>&1


    V systému je několik queue workerů, pro dohled cca 200 streamů je zapotřebí cca 200 workerů ciste pro dohled 
        příkaz: php artisan queue:work --sleep=1 --tries=1 --max-time=5

    Pro analýzu audia / videa, zde pro 200 streamu cca 50 workerů
        příkaz: php artisan queue:work redis --queue=ffprobe --sleep=3 --tries=1 --max-time=30
    
    Pro vytváření náhledů u 200 streamů cca 50 workerů
        příkaz: php artisan queue:work redis --queue=ffmpeg --sleep=3 --tries=1 --max-time=30

    Pro jednoduchou správu doporučuji program supervisor, který se instaluje příkazem sudo apt-get install supervisor
        - v /etc/supervisor/conf.d/ vytvořte několik .conf souborů, které chcete aby se spousteli hned po startu systému případně, kdyby nastal problém s procesem, aby jej sám znovu spustil

        příklad pro vytvoření souboru pro hlídání workerů
        
        ----------------------------------------------------------------------------------------------------------------------------------------
        [program:laravel-worker]
        process_name=%(program_name)s_%(process_num)02d
        command=php /var/www/html/laravel8_iptv_dohled/artisan queue:work --sleep=1 --tries=1 --max-time=5
        autostart=true
        autorestart=true
        stopasgroup=true
        killasgroup=true
        user=root
        numprocs=200
        redirect_stderr=true
        #stdout_logfile=/home/forge/app.com/worker.log
        stopwaitsecs=3600
        ----------------------------------------------------------------------------------------------------------------------------------------
        
        Načtení do supervisoru 
        sudo supervisorctl reread

        sudo supervisorctl update

        sudo supervisorctl start laravel-worker:* // nebo proces co chcete spustit


        SYSTÉMOVÉ POŽADAVKY
            - hodně se to odvíjí od počtu dohledovaných streamů, pro 6 streamech toto zvládně RASPI 4 s 32GB SD karty
            - pro 200 streamů minimálně 16 jader s takten 3,2GHz , 64GB RAM , SSD diskem.
            - VMWare je doporučen naopak nedoporučuji PROXMOX , který má tentenci chybovat na síťových kartách