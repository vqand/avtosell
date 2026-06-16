# 5VITO — Проектирование БД и ER-диаграмма

Нормализованная схема MySQL (3НФ), движок InnoDB, сравнение `utf8mb4_unicode_ci`
(полная поддержка кириллицы).

## Сущности

| Таблица       | Назначение                                            |
|---------------|-------------------------------------------------------|
| `roles`       | Справочник ролей (`admin`, `user`) для RBAC           |
| `users`       | Зарегистрированные учётные записи                     |
| `brands`      | Производители; `origin` = отечественный / иномарка    |
| `models`      | Модели, принадлежащие бренду                          |
| `cars`        | Объявления (основная сущность)                        |
| `car_images`  | Галерея фото автомобиля (один-ко-многим)              |
| `favorites`   | Пользователь ↔ Авто, многие-ко-многим (избранное)     |
| `reports`     | Жалобы пользователей на объявление (модерация)        |
| `admin_logs`  | Журнал аудита действий администраторов                |

## ER-диаграмма (Mermaid)

```mermaid
erDiagram
    roles ||--o{ users : "имеет"
    users ||--o{ cars : "продаёт"
    brands ||--o{ models : "имеет"
    brands ||--o{ cars : "категоризует"
    models ||--o{ cars : "категоризует"
    cars ||--o{ car_images : "имеет"
    users ||--o{ favorites : "сохраняет"
    cars ||--o{ favorites : "в избранном"
    cars ||--o{ reports : "получает жалобы"
    users ||--o{ reports : "подаёт"
    users ||--o{ admin_logs : "выполняет"

    roles {
        tinyint id PK
        varchar name UK
    }
    users {
        int id PK
        tinyint role_id FK
        varchar name
        varchar email UK
        varchar phone
        varchar password_hash
        tinyint is_blocked
        timestamp created_at
        timestamp updated_at
    }
    brands {
        smallint id PK
        varchar name UK
        enum origin
    }
    models {
        int id PK
        smallint brand_id FK
        varchar name
    }
    cars {
        int id PK
        int user_id FK
        smallint brand_id FK
        int model_id FK
        varchar title
        text description
        decimal price
        smallint year
        int mileage
        enum fuel_type
        enum transmission
        enum drive_type
        varchar body_type
        decimal engine_volume
        smallint power_hp
        varchar color
        tinyint owners_count
        varchar vin
        enum status
        tinyint is_active
        timestamp created_at
        timestamp updated_at
    }
    car_images {
        int id PK
        int car_id FK
        varchar url
        tinyint is_primary
        smallint sort_order
    }
    favorites {
        int user_id PK,FK
        int car_id PK,FK
        timestamp created_at
    }
    reports {
        int id PK
        int car_id FK
        int user_id FK
        varchar reason
        enum status
        timestamp created_at
    }
    admin_logs {
        int id PK
        int admin_id FK
        varchar action
        varchar entity_type
        int entity_id
        text details
        timestamp created_at
    }
```

## Связи

- `users.role_id → roles.id` (RESTRICT): у каждого пользователя ровно одна роль.
- `cars.user_id → users.id` (CASCADE): удаление пользователя удаляет его объявления.
- `cars.brand_id → brands.id` (RESTRICT): используемый бренд нельзя удалить.
- `cars.model_id → models.id` (SET NULL): модель необязательна.
- `car_images.car_id → cars.id` (CASCADE).
- Составной первичный ключ `favorites (user_id, car_id)` исключает дубликаты.
- `reports.car_id → cars.id` (CASCADE), `reports.user_id → users.id` (SET NULL).
- `admin_logs.admin_id → users.id` (CASCADE).

## Стратегия индексирования

| Индекс                                  | Обоснование                                  |
|-----------------------------------------|----------------------------------------------|
| `uq_users_email`                        | Поиск при входе + уникальность               |
| `idx_users_phone`                       | Вход по телефону (экран входа из макета)      |
| `idx_cars_status_active`                | Публичный каталог показывает только approved+active |
| `idx_cars_price`, `idx_cars_year`       | Фильтры по диапазону                          |
| `idx_cars_filter (brand_id,price,year)` | Составной индекс под частый комбинированный фильтр |
| `ft_cars_search (title,description)`    | `MATCH … AGAINST` для строки поиска           |
| `idx_brands_origin`                     | Фильтр «Отечественные / Иномарки»             |
| PK `favorites (user_id, car_id)`        | Переключение/дедупликация избранного за O(1)  |

## Замечания по нормализации

- **1НФ**: все столбцы атомарны; изображения галереи вынесены в `car_images`.
- **2НФ**: нет частичных зависимостей (одностолбцовые суррогатные ПК).
- **3НФ**: бренд/модель вынесены в отдельные таблицы; `origin` хранится в
  `brands`, а не дублируется в каждом авто.
