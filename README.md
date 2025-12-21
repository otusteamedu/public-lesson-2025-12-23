## Репозиторий к [открытому уроку](https://otus.ru/lessons/razrabotchik-php/#event-6667) курса [PHP Developer. Professional](https://otus.ru/lessons/razrabotchik-php/)

Автор: [Сергей Петров](mailto:cl@coders-lair.com)

## Что такое RoadRunner?

### Сборка проекта и зависимостей

1. Создаём и запускаем контейнеры командой `docker-compose up -d`
2. Подключаемся к контейнеру `php`: `docker exec -it pl-php bash`
3. В конейтенере устанавливаем зависимости: выполняем команду `composer install`

### Проверка работоспособности

1. В браузере работает `http://localhost:29998` с ответом
   ```json
   {
      "success": true,
      "data": "App\\Root\\Infrastructure\\Http\\Web\\RootController::rootIndex()"
   }
   ```
2. Из Postman-коллекции успешно работает запрос `http://localhost:29998/api/pdf/create` и в директории `var/files/` создаётся pdf-файл
