# 5VITO — Спецификация REST API

Базовый URL (разработка): `http://localhost:8080/api`

- Все тела запросов/ответов — в формате JSON (`Content-Type: application/json`),
  кроме загрузки изображений (`multipart/form-data`).
- Защищённые эндпоинты требуют заголовок `Authorization: Bearer <jwt>`.
- Формат ошибки: `{ "error": "сообщение", "fields": { ... } }` с
  соответствующим HTTP-статусом (`401`, `403`, `404`, `409`, `422`, `500`).

Обозначения доступа: «публичный» — токен не нужен · «вход» — требуется JWT ·
«админ» — только администратор.

---

## Аутентификация

| Метод  | Путь                  | Доступ | Тело / примечания |
|--------|-----------------------|--------|--------------|
| POST   | `/auth/register`      | публичный | `{name, email, password, phone?}` → `{token, user}` |
| POST   | `/auth/login`         | публичный | `{login, password}` — `login` = email или телефон → `{token, user}` |
| GET    | `/auth/me`            | вход   | → `{user}` |
| PUT    | `/auth/profile`       | вход   | `{name, phone?}` → `{user}` |

## Каталог (публичный)

| Метод  | Путь                | Доступ | Примечания |
|--------|---------------------|--------|-------|
| GET    | `/cars`             | публичный* | Поиск + фильтры (см. ниже) → `{data, meta}` |
| GET    | `/cars/{id}`        | публичный* | → `{car}` (включает фото, продавца) |
| POST   | `/cars/{id}/report` | публичный* | `{reason}` — подать жалобу |

\* Читает Bearer-токен, если он передан (чтобы пометить `is_favorite`), но не требует его.

**Параметры запроса `GET /cars`** (все необязательны):

| Параметр | Пример | Значение |
|----------|--------|----------|
| `q` | `bmw` | Полнотекстовый поиск (название + описание) |
| `brand_ids` | `1,3` | Один или несколько ID брендов (через запятую) |
| `model_id` | `4` | Фильтр по модели |
| `origin` | `domestic` \| `foreign` | Происхождение бренда |
| `price_min` / `price_max` | `500000` | Диапазон цены |
| `year_min` / `year_max` | `2020` | Диапазон года |
| `mileage_max` | `100000` | Максимальный пробег |
| `fuel_type` | `petrol` | `petrol\|diesel\|hybrid\|electric\|gas` |
| `transmission` | `automatic` | `manual\|automatic\|robot\|variator` |
| `drive_type` | `full` | `rear\|front\|full` |
| `sort` | `price_asc` | `new\|price_asc\|price_desc\|year_desc` |
| `page` / `per_page` | `1` / `12` | Пагинация (макс. 60) |

Ответ:
```json
{
  "data": [ { "id": 2, "title": "...", "price": "6100000.00", "image": "/uploads/..", "is_favorite": false } ],
  "meta": { "total": 6, "page": 1, "per_page": 12, "pages": 1 }
}
```

## Бренды и модели (публичные)

| Метод  | Путь                    | Примечания |
|--------|-------------------------|-------|
| GET    | `/brands`               | → `{data}` (с `cars_count`) |
| GET    | `/brands/{id}/models`   | → `{data}` |
| GET    | `/models`               | → `{data}` (все, с названием бренда) |

## Управление объявлениями

| Метод  | Путь                  | Доступ | Примечания |
|--------|-----------------------|--------|-------|
| GET    | `/my/cars`            | вход   | Объявления текущего пользователя (любой статус) |
| POST   | `/cars`               | вход   | Создание объявления (публикуется сразу, статус `approved`) |
| PUT    | `/cars/{id}`          | вход†  | Обновление объявления |
| DELETE | `/cars/{id}`          | вход†  | Удаление объявления |
| POST   | `/cars/{id}/images`   | вход†  | `multipart/form-data`, поле `image` |

† Только владелец или администратор.

Тело создания/обновления автомобиля:
```json
{
  "title": "BMW M5 4.4 AT, 2026",
  "brand_id": 1, "model_id": 1,
  "price": 21000000, "year": 2026, "mileage": 0,
  "fuel_type": "petrol", "transmission": "automatic", "drive_type": "full",
  "body_type": "Седан", "engine_volume": 4.4, "power_hp": 625,
  "color": "Чёрный", "owners_count": 1, "vin": "WBS...", "description": "..."
}
```

## Избранное

| Метод  | Путь                   | Доступ | Примечания |
|--------|------------------------|--------|-------|
| GET    | `/favorites`           | вход   | → `{data}` |
| POST   | `/favorites/{carId}`   | вход   | Добавить |
| DELETE | `/favorites/{carId}`   | вход   | Удалить |

---

## Администрирование — `/api/admin/*` (только администратор)

| Метод  | Путь                          | Примечания |
|--------|-------------------------------|-------|
| GET    | `/admin/dashboard`            | `{stats, recent_cars, recent_logs}` |
| GET    | `/admin/users`                | Список пользователей |
| PUT    | `/admin/users/{id}`           | `{name, phone?, role_id}` |
| PATCH  | `/admin/users/{id}/block`     | `{blocked: true\|false}` |
| DELETE | `/admin/users/{id}`           | Удалить пользователя |
| GET    | `/admin/cars`                 | Все объявления (фильтр `?status=`) |
| PATCH  | `/admin/cars/{id}/status`     | `{status: pending\|approved\|rejected}` |
| DELETE | `/admin/cars/{id}`            | Удалить объявление |
| GET    | `/admin/reports`              | Список жалоб |
| PATCH  | `/admin/reports/{id}`         | `{status: open\|reviewed\|dismissed}` |
| POST   | `/admin/brands`               | `{name, origin}` |
| PUT    | `/admin/brands/{id}`          | `{name, origin}` |
| DELETE | `/admin/brands/{id}`          | Удалить бренд (409, если есть связанные объявления) |
| POST   | `/admin/models`               | `{brand_id, name}` |

## Служебные

| Метод  | Путь           | Примечания |
|--------|----------------|-------|
| GET    | `/health`      | → `{status: "ok"}` |
