## Репозиторий к [открытому уроку](https://otus.ru/lessons/razrabotchik-php/#event-6667) курса [PHP Developer. Professional](https://otus.ru/lessons/razrabotchik-php/)

Автор: [Сергей Петров](mailto:cl@coders-lair.com)

## Что такое RoadRunner?

### Сборка проекта и зависимостей

1. Создаём и запускаем контейнеры командой `docker-compose up -d`
2. Подключаемся к контейнеру `pl-php`: `docker exec -it pl-php bash`
3. В контейнере устанавливаем зависимости: выполняем команду `composer install`

### Проверка работоспособности

1. В браузере работает `http://localhost:29998` с ответом
   ```json
   {
      "success": true,
      "data": "App\\Root\\Infrastructure\\Http\\Web\\RootController::rootIndex()"
   }
   ```
2. Из Postman-коллекции успешно работает запрос `http://localhost:29998/api/pdf/create` и в директории `var/files/`
   создаётся pdf-файл

## Добавляем RoadRunner

1. Заходим в контейнер `pl-php`:
   ```shell
   docker exec -it pl-php bash
   ```
2. Устанавливаем пакеты:
   ```shell
   composer require spiral/roadrunner-http spiral/roadrunner-cli
   ```
3. В директорию `/docker/rr` добавляем `Dockerfile`:
   ```dockerfile
   FROM ghcr.io/roadrunner-server/roadrunner:latest AS roadrunner
   FROM php:8.3-cli
   
   COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
   
   WORKDIR /app
   
   CMD rr serve -c .rr.yaml
   ```
4. В директорию `/docker/rr` добавляем конфигурационный файл `.rr.yaml`:
   ```yaml
   version: "3"
   
   server:
     command: "php /src/RR/psr-worker.php"
   
   http:
     address: 0.0.0.0:29999
   
   logs:
     level: debug
     mode: development
   ```
5. Добавляем контейнер в файл `docker-compose.yaml`:
   ```yaml
    rr:
        build: ./docker/rr
        container_name: 'pl-rr'
        volumes:
            -  ./app:/app
            -  ./docker/rr/.rr.yaml:/app/.rr.yaml:delegated
        working_dir: /app
        ports:
            - '29999:29999'
   ```
6. Создаём файл `src/RR/psr-worker.php`
   ```php
   <?php
   
   require '/app/vendor/autoload.php';
   
   use CodersLairDev\ClFw\Kernel\Kernel;
   use Nyholm\Psr7\Response;
   use Nyholm\Psr7\Factory\Psr17Factory;
   use Spiral\RoadRunner\Http\PSR7Worker;
   
   
   $worker = \Spiral\RoadRunner\Worker::create();
   
   $factory = new Psr17Factory();
   
   $psr7 = new PSR7Worker($worker, $factory, $factory, $factory);
   
   $projectDir = dirname(__DIR__);
   
   $config = [
       'services' => [
           [
               'path' => 'Root',
               'namespace' => 'App\Root',
           ],
           [
               'path' => 'Pdf',
               'namespace' => 'App\Pdf',
           ],
       ]
   ];
   
   $kernel = new Kernel($projectDir, $config);
   
   while (true) {
       try {
           $request = $psr7->waitRequest();
   
           if ($request === null) {
               break;
           }
       } catch (Throwable $e) {
           $psr7->respond(new Response(400));
           continue;
       }
   
       try {
           $response = $kernel->handle($request, true);
           $psr7->respond($response);
       } catch (Throwable $e) {
           $psr7->respond(new Response(500, [], 'Something Went Wrong!'));
           $psr7->getWorker()->error((string)$e);
       }
   }
   ```
7. Останавливаем все контейнеры:
   ```shell
   docker compose down --remove-orphans
   ```
8. И запускаем заново, с учётом новых:
   ```shell
   docker compose up -d
   ```
9. Выполняем запрос из Postman-коллекции `OK PDF (RR)`, видим, что файлы создаются. При некоторых условиях
   производительности хост-машины можно заметить даже здесь, что исполнение стало несколько быстрее

## Добавляем стресс-тест

1. Создаём директорию `app/tests`
2. Подключаемся к контейнеру `pl-php`: `docker exec -it pl-php bash`
3. Устанавливаем Pest: 
   ```shell
   composer require pestphp/pest pestphp/pest-plugin-stressless --dev --with-all-dependencies
   ```
4. Выполняем в контейнере команду:
   ```shell
   pestphp/pest-plugin-stressless
   ```
5. Исправляем контроллер `App\Root\Infrastructure\Http\Web\RootController`:
   ```php
   <?php
   
   namespace App\Root\Infrastructure\Http\Web;
   
   use CodersLairDev\ClFw\Http\Response\Trait\ResponseTrait;
   use CodersLairDev\ClFw\Routing\Attribute\AsController;
   use CodersLairDev\ClFw\Routing\Attribute\AsRoute;
   use Monolog\Handler\StreamHandler;
   use Monolog\Logger;
   use Nyholm\Psr7\Factory\Psr17Factory;
   use Psr\Http\Message\ResponseInterface;
   use Psr\Log\LoggerInterface;
   
   #[AsController]
   class RootController
   {
       use ResponseTrait;
   
       private LoggerInterface $logger;
       private Psr17Factory $psr17Factory;
   
       public function __construct()
       {
           $this->psr17Factory = new Psr17Factory();
   
           $this->logger = new Logger('sha1_log');
           $this->logger->pushHandler(new StreamHandler('/app/var/log/sha1_log.log'));
       }
   
       #[AsRoute(path: '/')]
       public function rootIndex(): ResponseInterface
       {
           $data = [
               'success' => true,
               'data' => __CLASS__ . '::' . __FUNCTION__ . '()',
           ];
   
           return $this->createResponse(
               psr17Factory: new Psr17Factory(),
               content: json_encode($data),
               status: 200
           );
       }
   
       #[AsRoute(path: '/getSha1Hash')]
       public function getSha1Hash(): ResponseInterface
       {
           $requestId = uniqid();
           $requestSha1Hash = sha1($requestId);
   
           $data = [
               'requestId' => $requestId,
               'requestSha1Hash' => $requestSha1Hash,
           ];
   
           $this->logger->info('getSha1Hash', $data);
   
           return $this->createResponse(
               psr17Factory: $this->psr17Factory,
               content: json_encode($data),
               status: 200
           );
       }
   }
   ```
6. Перезапускаем контейнер с RR:
   ```shell
   docker container restart pl-rr
   ```
7. Из Postman-коллекции пробуем выполнить запросы `OK /getSha1Hash` и `OK (RR) /getSha1Hash`. Видим, что всё работает, ответы приходят.
8. В контейнере `pl-php`:
   ```shell
   ./vendor/bin/pest stress http://pl-nginx/getSha1Hash --concurrency=100
   ./vendor/bin/pest stress http://pl-rr:29999/getSha1Hash --concurrency=100
   ```
   Видим разницу во времени ответов
9. Запускаем тест для RR с увеличенным `concurrency`:
   ```shell
   ./vendor/bin/pest stress http://pl-rr:29999/getSha1Hash --concurrency=500
   ```
   Видим, что процесс падает с нехваткой памяти
10. Исправляем метод `getSha1Hash` в контроллере `App\Root\Infrastructure\Http\Web\RootController`:
   ```php
   #[AsRoute(path: '/getSha1Hash')]
       public function getSha1Hash(): ResponseInterface
       {
           $requestId = uniqid();
           $requestSha1Hash = sha1($requestId);

           $this->logger->info('getSha1Hash', [
              'requestId' => $requestId,
              'requestSha1Hash' => $requestSha1Hash,
           ]);
   
           return $this->createResponse(
               psr17Factory: new Psr17Factory(),
               content: json_encode([
                   'requestId' => $requestId,
                   'requestSha1Hash' => $requestSha1Hash,
               ]),
               status: 200
           );
       }
```
11. Перезапускаем контейнер RR:
   ```shell
   docker container restart pl-rr
   ```
12. Запускаем тест ещё раз