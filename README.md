# 2324_ATP_routeyou_2_backend

## Running and stopping the Docker MCE

* Run the environment, using Docker, from your terminal/cmd
```shell
cd <your-project>
docker-compose up
```
* Stop the environment in your terminal/cmd by pressing <code>Ctrl+C</code>
* In order to avoid conflicts with your lab/slides environment, run from your terminal/cmd
```shell
docker-compose down
```

## Quick configuration of the Laravel project

You still need to install the libraries in the vendor folder (because they are not on git) and do some basic configuration like generating the key, linking the storage &hellip;
* Run from your terminal/cmd
```shell
docker-compose exec -u 1000:1000 php-web bash
$ cp .env.example .env
$ composer install
$ php artisan key:generate
$ touch storage/logs/laravel.log
$ chmod 777 -R storage bootstrap/cache
$ php artisan storage:link
$ exit
```


## Recipes and troubleshooting

### I still have problems with file permissions (WSL2)
* Symptoms: web server or artisan has still errors when writing to file system
* Run from your WLS2 Ubuntu terminal
```shell
$ sudo chown yourname:yourname -R .
```

### <code>docker-compose up</code> does not start one or more containers
* Look at the output of <code>docker-compose up</code>. When a container (fails and) exits, it is shown as the last line of the container output (colored tags by container)
* Alternatively, start another terminal/cmd and inspect the output of <code>docker-compose ps -a</code>. You can see which container exited, exactly when.
* Probably one of the containers fails because TCP/IP port 8000, 8080 or 3307 is already in use on your system. Stop the environment, change the port in <code>docker-compose.yml</code> en rerun <code>docker-compose up</code>.


