# Система обработки заказов билетов

## 1. Описание

Система предназначена для обработки заказов билетов на события с поддержкой различных типов билетов,
ценообразования и интеграции с условным API.

## 2. Архитектура

### 2.1 Структура базы данных
[migration.sql](migration.sql)

### 2.2 Компоненты системы

#### 2.2.1 Сущности
- [`Event`](src/Entities/Event.php) - событие
- [`TicketType`](src/Entities/TicketType.php) - тип билета
- [`EventPrice`](src/Entities/EventPrice.php) - цена билета (формируют историю цен на событие)
- [`Order`](src/Entities/Order.php) - заказ
- [`Ticket`](src/Entities/Ticket.php) - билет с уникальным barcode'ом

#### 2.2.2 Сервисы
- [`OrderProcessor`](src/Services/OrderProcessor.php)
  - Класс управления процессом заказа
  - Основные методы:
    - createOrder(): Создание заказа
    - getEvent(): Получение события
    - getTicketType(): Получение типа билета
    - getActualPrice(): Получение актуальной цены
- [`ApiClient`](src/Services/ApiClient.php)
    - Симуляция внешнего API для бронирования и подтверждения заказов
    - Методы:
        - bookOrder(): Бронирование заказа
        - approveOrder(): Подтверждение заказа

#### 2.2.3 Утилиты
- [`Barcode`](src/Utils/Barcode.php)
    - Утилита генерации уникальных barcode'ов
    - Метод generateUnique()

## 3. Функциональность

### 3.1 Управление событиями
```bash
# Создание события и заказа
php src/index.php --create-event EVENT_NAME EVENT_DESCRIPTION TICKET_TYPE PRICE QUANTITY
php index.php --create-event "Woodstock 1969" "The Woodstock Music and Art Fair" "adult" 1000 2

# Создание заказа
php src/index.php EVENT_NAME TICKET_TYPE QUANTITY
php index.php "Woodstock 1969" "adult" 2
```
Пример вывода:
```bash
Order 10 created successfully with tickets (barcodes):
- 13160770
- 83520204
- 53188569
- 46912255
- 86190362
````

### 3.2 Управление ценами
- Поддержка истории цен через event_prices.valid_to
- Автоматическое обновление при изменении цены (при prompt'е с флагом --create-event)
- Проверка актуальности цены при создании заказа

### 3.3 Процесс создания заказа
1. Проверка существования события
2. Проверка типа билета
3. Проверка актуальной цены
4. Генерация уникальных barcode'ов
5. Бронирование через API
6. Подтверждение через API
7. Сохранение заказа и его билетов

## 4. Обработка ошибок

### 4.1 Типы исключений
- [`ValidationException`](src/Exceptions/ValidationException.php) - ошибки валидации входных данных
- [`DatabaseException`](src/Exceptions/DatabaseException.php) - ошибки базы данных
- [`EntityNotFoundException`](src/Exceptions/EntityNotFoundException.php) - сущность не найдена
- [`EntityCreationException`](src/Exceptions/EntityCreationException.php) - ошибка создания сущности
- [`ApiException`](src/Exceptions/ApiException.php) - ошибки API
- [`FsException`](src/Exceptions/FsException.php) - ошибка файловой системы

## 5. API

### 5.1 Бронирование
```php
$req = [
    "event_id" => $eventId,
    "event_date" => $event->getDate(),
    "ticket_price" => $actualPrice->getPrice(),
    "barcode" => $barcode,
];

$res = $this->apiClient->bookOrder($req);
```

### 5.2 Подтверждение
```php
$res = $this->apiClient->approveOrder($barcode);
```

## 6. Тестирование

### 6.1 Модульные тесты
- [OrderProcessorTest](tests/Services/OrderProcessorTest.php)
- [ApiClientTest](tests/Services/ApiClientTest.php)
- [BarcodeTest](tests/Utils/BarcodeTest.php)

### 6.2 Запуск тестов
```bash
composer test
```

## 7. Установка и настройка

### 7.1 Требования
- PHP 8.3+
- PostgreSQL
- Composer
- Зависимости:
    - PDO
    - Opis Database
    - Opis ORM

### 7.2 Установка
```bash
composer install
```

### 7.3 Конфигурация
- Настройка подключения к БД в dbConfig.json
```json
{
  "host": "localhost",
  "dbName": "testorderprocessor", 
  "user": "postgres",
  "password": "12345678"
}
```

# Аргументация по задаче нормализации для добавления типов билетов и уникальных barcode'ов:

## Для добавления различных типов билетов:

- Я создал таблицу ticket_types для хранения типов билетов (обычный, льготный, групповой)
- В таблице event_prices связываю события с типами билетов и их ценами
- Это позволяет легко добавлять новые типы билетов в будущем без изменения структуры базы данных
- Гибкость достигается за счет связей между таблицами events, ticket_types и event_prices


## Для реализации уникального barcode'а для каждого билета:

- Создал таблицу tickets, где каждый билет имеет собственный уникальный barcode
- Связь с orders и event_prices позволяет хранить полную информацию о каждом билете
- Поле is_used дает возможность отслеживать статус использования билета
- При создании заказа с несколькими билетами будут созданы записи в таблице tickets, каждая со своим barcode'ом 



## Преимущества такого подхода:

- Полная нормализация данных
- Возможность расширения типов билетов
- Индивидуальный учет каждого билета
