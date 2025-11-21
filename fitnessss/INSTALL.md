# Инструкция по установке

## Быстрый старт

### 1. Требования

- PHP 7.4 или выше
- MySQL 5.7 или выше (или MariaDB 10.3+)
- Веб-сервер (Apache/Nginx) или встроенный PHP сервер
- PHP расширения: PDO, PDO_MySQL, mbstring

### 2. Установка базы данных

#### Вариант А: Через командную строку

```bash
mysql -u root -p < database/schema.sql
```

#### Вариант Б: Через phpMyAdmin

1. Откройте phpMyAdmin
2. Создайте новую базу данных `fitness_center`
3. Выберите эту базу данных
4. Перейдите на вкладку "Импорт"
5. Выберите файл `database/schema.sql`
6. Нажмите "Выполнить"

#### Вариант В: Через MySQL Workbench

1. Откройте MySQL Workbench
2. Подключитесь к серверу
3. Откройте файл `database/schema.sql`
4. Выполните скрипт (Ctrl+Shift+Enter)

### 3. Настройка подключения к БД

Откройте файл `config/database.php` и измените следующие строки:

```php
define('DB_HOST', 'localhost');        // Хост БД
define('DB_NAME', 'fitness_center');   // Имя БД
define('DB_USER', 'root');             // Пользователь БД
define('DB_PASS', '');                 // Пароль БД
```

### 4. Настройка веб-сервера

#### Вариант А: Встроенный PHP сервер (для разработки)

```bash
php -S localhost:8000
```

Затем откройте браузер: `http://localhost:8000`

#### Вариант Б: Apache

1. Скопируйте папку проекта в `htdocs` (или другую директорию Apache)
2. Убедитесь, что Apache настроен для обработки PHP
3. Откройте браузер: `http://localhost/fitness-center`

#### Вариант В: Nginx

Пример конфигурации:

```nginx
server {
    listen 80;
    server_name fitness-center.local;
    root /path/to/fitness-center;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 5. Проверка установки

1. Откройте сайт в браузере
2. Попробуйте зарегистрироваться
3. Или войдите с тестовыми данными:
   - **Админ**: `admin` / `password`
   - **Пользователь**: `user1` / `password`

### 6. Возможные проблемы

#### Ошибка подключения к БД

- Проверьте правильность данных в `config/database.php`
- Убедитесь, что MySQL сервер запущен
- Проверьте права доступа пользователя БД

#### Ошибка 500 (Internal Server Error)

- Проверьте логи PHP (обычно в `/var/log/apache2/error.log` или аналогичном)
- Убедитесь, что все расширения PHP установлены
- Проверьте права доступа к файлам

#### Страница не найдена (404)

- Проверьте правильность путей в конфигурации веб-сервера
- Убедитесь, что `.htaccess` поддерживается (для Apache)
- Проверьте настройки `rewrite` модуля

### 7. Первоначальная настройка

После установки рекомендуется:

1. Изменить пароль администратора
2. Настроить email для уведомлений (если необходимо)
3. Настроить права доступа к файлам:
   ```bash
   chmod 644 *.php
   chmod 755 admin/ api/ config/
   ```

### 8. Структура проекта

Проект организован следующим образом:

- `/admin/` - административная панель
- `/api/` - API endpoints
- `/assets/` - статические файлы (CSS, JS)
- `/config/` - конфигурационные файлы
- `/database/` - SQL схемы
- `/includes/` - общие компоненты (header, footer)
- `/tests/` - тесты

### 9. Дополнительная информация

Для подробной документации см. `README.md`

Для вопросов и проблем обращайтесь к документации проекта.

