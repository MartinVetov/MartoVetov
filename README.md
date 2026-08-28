# Намери Техник

Платформа за наем на специализирана техника в България.

Основната идея не е каталог с обяви, а **lead generation**: клиентът описва какво
трябва да свърши, системата преценява каква техника е нужна и насочва заявката към
подходящи доставчици в неговия район.

> **Другите сайтове:** „Ето списък с фирми. Обади им се.“
> **Намери Техник:** „Кажи какво трябва да свършиш. Ние ще намерим подходящата техника и доставчик.“

---

## Съдържание

- [Технологичен стек](#технологичен-стек)
- [Бърз старт](#бърз-старт)
- [Достъп след seed](#достъп-след-seed)
- [Архитектура](#архитектура)
- [Matching алгоритъм](#matching-алгоритъм)
- [SEO структура](#seo-структура)
- [Бизнес модел](#бизнес-модел)
- [Сигурност и GDPR](#сигурност-и-gdpr)
- [Тестове](#тестове)
- [Deployment](#deployment)
- [Следващи фази](#следващи-фази)

---

## Технологичен стек

| Слой | Технология |
|------|-----------|
| Backend | PHP 8.3+ / Laravel 12 |
| База данни | MySQL 8+ (SQLite за тестовете) |
| Шаблони | Laravel Blade + Blade компоненти |
| Стилове | Tailwind CSS 4 (през Vite) |
| Интерактивност | Alpine.js 3 |
| Опашки | Laravel Queues (database драйвер) |
| Известия | Laravel Notifications / Mail |

Цялата бизнес логика е в PHP. JavaScript се използва само за интерфейсни
взаимодействия — многостъпковата форма, мобилното меню, FAQ акордеона.

---

## Бърз старт

```bash
# 1. Зависимости
composer install
npm install

# 2. Конфигурация
cp .env.example .env
php artisan key:generate

# 3. Създай базата в MySQL
mysql -u root -p -e "CREATE DATABASE nameri_tehnik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# и попълни DB_* в .env

# 4. Таблици и примерни данни
php artisan migrate --seed

# 5. Връзка към публичното хранилище за качените снимки
php artisan storage:link

# 6. Frontend
npm run build      # или: npm run dev — за разработка

# 7. Локален сървър
php artisan serve
```

### Стартиране

```bash
composer dev
```

Пуска едновременно сървъра, обработката на опашката и Vite. Отвори http://localhost:8000.

Или всяко поотделно:

```bash
php artisan serve       # уеб сървър
php artisan queue:work  # имейли към доставчиците и администратора
npm run dev             # компилиране на стиловете с hot reload
```

За следене на логовете в реално време: `composer logs`
(изисква разширението `pcntl`, което липсва на Windows — там чети
`storage/logs/laravel.log` направо).

---

## Достъп след seed

| Роля | Имейл | Парола |
|------|-------|--------|
| Администратор | `admin@nameritehnik.bg` | `parola123` |
| Доставчик | `ivan@stroytehnik.bg` | `parola123` |

Паролата на администратора се задава чрез `NT_ADMIN_PASSWORD` в `.env`.
**Смени я преди пускане в реална среда.**

Seed-ът създава 28 града, 8 категории с подтипове, 16 доставчика с техника,
12 заявки в различни състояния и примерни отзиви.

---

## Архитектура

```
app/
├── Console/Commands/       nt:anonymize-leads, nt:export-personal-data (GDPR)
├── Enums/                  LeadStatus, OperatorRequirement, LeadDuration, …
├── Http/
│   ├── Controllers/        публична част, Provider/, Admin/, Auth/
│   ├── Middleware/         роли, наличие на профил, проследяване на посещения
│   └── Requests/           валидация (Form Requests)
├── Jobs/                   ProcessNewLead
├── Models/                 Eloquent модели
├── Notifications/          имейли към доставчик, администратор, клиент
├── Policies/               авторизация на ниво модел
├── Rules/                  BulgarianPhone, Turnstile
├── Services/
│   ├── LeadMatchingService     подбор и оценяване на доставчици
│   ├── LeadDispatchService     изпращане на заявка и история
│   ├── LeadIntakeService       приемане на заявка, снимки, дубликати
│   ├── BillingService          такса за приета заявка
│   ├── Payments/               PaymentGateway + ManualGateway + PaymentManager
│   ├── AnalyticsService        събития по фунията
│   ├── ImageUploadService      безопасно качване и преоразмеряване
│   └── TurnstileService        защита от ботове
└── Support/CategoryAttributes  динамични полета според категорията
```

Сложната логика живее в Service класове, а не в контролери или шаблони.
Разрешенията се проверяват на сървъра чрез middleware и Policies —
скриването на бутони във фронтенда не е защита.

### Основни модели и връзки

```
User ──hasOne──> ProviderProfile ──hasMany──> Equipment ──hasMany──> EquipmentImage
                        │                          └─belongsTo─> EquipmentCategory
                        ├──hasMany──> ProviderServiceArea ──belongsTo──> City
                        ├──hasMany──> Review
                        ├──hasMany──> Subscription / Payment
                        └──belongsTo─> City

Lead ──belongsTo──> EquipmentCategory, City, User (customer)
     ──hasMany────> LeadImage
     ──belongsToMany(ProviderProfile) през lead_provider
```

`lead_provider` е основната таблица за фунията: пази кога заявката е изпратена,
прегледана, приета, кога доставчикът се е свързал и кога работата е завършена.

### Основни потоци

**Клиент** → начална страница → „Намери техника“ → 7 стъпки → потвърждение с номер на заявка.

**Заявка** → запис в базата → `ProcessNewLead` → известие към администратора →
администраторът вижда класирани доставчици → избира и изпраща → доставчиците получават имейл.

**Доставчик** → регистрация → профил на фирмата → техника → обслужвани райони →
изпраща за одобрение → администраторът одобрява → получава заявки → приема/отказва →
свързва се с клиента → отбелязва като завършена → клиентът получава покана за отзив.

---

## Matching алгоритъм

`App\Services\LeadMatchingService` оценява всеки допустим доставчик и връща
подредена ранглиста с обяснение на български защо е предложен.

| Критерий | Точки по подразбиране |
|----------|----------------------|
| Техника в исканата категория | 30 |
| Съвпадащ тип (напр. „2–5 тона“) | 15 |
| Базиран в града на заявката | 30 |
| Обявил е града като обслужван район | 25 |
| В радиуса на обслужване (по координати) | 20 |
| Същата област | 10 |
| Покрива изискването за оператор | 10 |
| Проверен доставчик | 10 |
| Рейтинг (пропорционално) | до 5 |
| Абонаментен план | 0 / 5 / 10 |

Тежестите са в `config/matching.php` и се презаписват през `.env`
(`MATCH_W_*`, `MATCH_MINIMUM_SCORE`, `MATCH_SUGGESTED_PROVIDERS`).

В MVP изпращането е **ръчно** — администраторът избира от ранглистата.
Автоматичното изпращане се включва с `MATCH_AUTO_DISPATCH=true`.

Допустими са само доставчици, които са едновременно активни, одобрени,
неблокирани и с активна техника в категорията.

---

## SEO структура

```
/                                   начална страница
/tehnika                            всички категории
/tehnika/mini-bager                 landing page на категория
/tehnika/mini-bager/sofia           локална landing page
/dostavchitsi                       каталог на доставчиците
/dostavchik/{slug}                  публичен профил
/kak-raboti, /za-dostavchitsi       информационни страници
/sitemap.xml, /robots.txt           динамични
```

Всяка публична страница има уникален `<title>`, meta description, canonical,
Open Graph тагове, breadcrumbs и Schema.org разметка (`Organization`,
`Service`, `LocalBusiness`, `BreadcrumbList`, `FAQPage`).

Локалните страници се генерират **само** за градове с включен флаг
`landing_enabled` и реално съдържание. Това е нарочно решение — стотици
почти еднакви страници вредят повече, отколкото помагат.

SEO текстът и въпросите за всяка категория се редактират от администрацията.

---

## Бизнес модел

**Pay per lead.** Доставчикът плаща за получена заявка. Цената **не е** зашита в
кода — задава се за всяка категория в администрацията, а резервната стойност
идва от `NT_LEAD_PRICE_DEFAULT`.

Таксата се начислява при **приемане** на заявката (`BillingService`), не при
изпращането ѝ.

Абонаментните планове (`config/nt.php`) определят месечна квота заявки, брой
машини, брой снимки и приоритет при подреждането. Онлайн плащания не са
реализирани в MVP — `PaymentGateway` интерфейсът и `PaymentManager` позволяват
Stripe, myPOS или банков превод да се добавят, без да се пипа бизнес логиката.

---

## Сигурност и GDPR

- CSRF защита на всички форми, XSS екраниране от Blade, параметризирани заявки през Eloquent
- Хеширане на паролите (bcrypt), rate limiting на вход, заявки и отзиви
- Authorization policies за техника, профили и заявки
- Проверка на качените файлове по тип, размер и реално съдържание; генерирани имена
- Anti-spam: Cloudflare Turnstile (по избор), скрито поле-примамка, минимално време за
  попълване, откриване на дубликати, валидация на български телефонни номера
- Данните за контакт на клиента се отключват едва след приемане на заявката
- Общи условия и Политика за поверителност
- `php artisan nt:anonymize-leads` — заличава лични данни след срока за съхранение
  (`NT_RETENTION_DAYS`), пуска се ежедневно от scheduler-а
- `php artisan nt:export-personal-data {имейл|телефон}` — за искане за достъп до данни

---

## Тестове

```bash
php artisan test
```

Покрити са: регистрация и вход, роли и права, създаване и валидация на заявка,
защита от спам и дубликати, matching алгоритъмът, изпращането към доставчици,
профил и техника на доставчика, приемане на заявка и начисляване на такса,
администраторските действия, отзивите с модерация, SEO страниците, преводите
на български и GDPR процесите.

Тестовете използват SQLite в паметта и не изискват работеща MySQL.

---

## Deployment

Проектът работи на стандартен PHP хостинг — не изисква контейнери или cloud инфраструктура.

### 1. Изисквания на сървъра

- PHP 8.3 или по-нов с разширения: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`,
  `xml`, `ctype`, `json`, `fileinfo`, `gd` (за преоразмеряване на снимки)
- MySQL 8.0+ (или MariaDB 10.6+)
- Composer 2
- Node.js 20+ **само за компилиране на статичните файлове** (може и локално)

### 2. Качване и зависимости

```bash
git clone <repo> /var/www/nameritehnik
cd /var/www/nameritehnik
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # или качи готовата public/build директория
```

### 3. Конфигурация

```bash
cp .env.example .env
php artisan key:generate
```

В `.env` задай: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`,
`DB_*`, `MAIL_*`, `NT_ADMIN_EMAIL`, `NT_ADMIN_PASSWORD` и ключовете за Turnstile.

### 4. База данни

```bash
php artisan migrate --force
php artisan db:seed --class=CitySeeder --force
php artisan db:seed --class=EquipmentCategorySeeder --force
php artisan db:seed --class=AdminSeeder --force
```

Не пускай пълния seeder в production — той създава примерни доставчици и заявки.

### 5. Права и връзки

```bash
php artisan storage:link
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 6. Кеширане за production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

След всяка промяна по кода изпълни отново трите команди.

### 7. Опашки

Имейлите се изпращат през опашка. Настрой supervisor:

```ini
[program:nameritehnik-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nameritehnik/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/nameritehnik/storage/logs/worker.log
stopwaitsecs=3600
```

Без supervisor може да ползваш cron: `* * * * * php artisan queue:work --stop-when-empty`.

### 8. Scheduler

```cron
* * * * * cd /var/www/nameritehnik && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Уеб сървър

**Nginx:**

```nginx
server {
    listen 80;
    server_name nameritehnik.bg www.nameritehnik.bg;
    root /var/www/nameritehnik/public;

    index index.php;
    charset utf-8;
    client_max_body_size 8M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

**Apache** — насочи DocumentRoot към `public/` и се увери, че `mod_rewrite` е
включен и `AllowOverride All` е зададен. `public/.htaccess` върши останалото.

Накрая пусни HTTPS (Let's Encrypt) — в production приложението форсира `https://`.

---

## Следващи фази

**Фаза 2** — автоматичен matching, онлайн оферти, чат, сравнение на оферти,
онлайн резервация, календар за наличност.

**Фаза 3** — онлайн плащания, комисиона върху сделката, абонаментни планове,
featured доставчици, premium заявки, автоматично фактуриране.

**Фаза 4** — разширяване към кранове, транспорт, земеделска и индустриална
техника, озеленяване, извозване, специализиран транспорт.
