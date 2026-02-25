### Library API

REST-API проект Библиотека книг.

### Технологии: 

- PHP 8.2
- MySQL 8.0
- Docker + Docker Compose
- Apache
- Composer

### Функциональность 

- Регистрация и авторизация (token-based)
- Управление книгами (CRUD + soft delete)
- Выдача доступа к библиотекам других пользователей
- Поиск книг через Google Books API
- Импорт найденных книг
- Middleware для проверки токенов

Проект построен по паттерну MVC с использованием сервисного слоя:

- **Controllers** - обработка запросов
- **Services** - бизнес-логика
- **Models** - работа с БД
- **Core** - базовые классы (Router, Database,  Middleware)

Команды для разработки

# Запустить контейнеры
docker-compose up -d

# Остановить контейнеры
docker-compose down

# Посмотреть логи
docker logs library_php

# Зайти в контейнер
docker exec -it library_php bash

# Выполнить миграции
docker exec -it library_php php migrate.php

Тестировал API-запросы с помощью EchoAPI - тесты сохранил в api-test/collections/apiList.json

