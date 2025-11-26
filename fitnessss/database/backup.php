<?php
/**
 * Скрипт для резервного копирования базы данных
 * Использование: php database/backup.php
 * Или откройте в браузере: http://your-site.com/database/backup.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Настройки
$backup_dir = __DIR__ . '/backups';
$backup_prefix = 'fitness_center_backup_';
$max_backups = 10; // Максимальное количество резервных копий

// Создаем папку для бэкапов, если её нет
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

try {
    $pdo = getDBConnection();
    
    // Получаем список всех таблиц
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    if (empty($tables)) {
        die("База данных пуста. Нет таблиц для резервного копирования.");
    }
    
    // Имя файла бэкапа
    $backup_file = $backup_dir . '/' . $backup_prefix . date('Y-m-d_H-i-s') . '.sql';
    $handle = fopen($backup_file, 'w');
    
    if (!$handle) {
        die("Не удалось создать файл резервной копии.");
    }
    
    // Заголовок SQL файла
    fwrite($handle, "-- Резервная копия базы данных fitness_center\n");
    fwrite($handle, "-- Дата создания: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "-- Версия MySQL: " . $pdo->query('SELECT VERSION()')->fetchColumn() . "\n\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
    
    // Экспорт каждой таблицы
    foreach ($tables as $table) {
        fwrite($handle, "-- Структура таблицы `$table`\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
        
        // Получаем CREATE TABLE
        $create_table = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        fwrite($handle, $create_table['Create Table'] . ";\n\n");
        
        // Получаем данные
        $stmt = $pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            fwrite($handle, "-- Данные таблицы `$table`\n");
            fwrite($handle, "LOCK TABLES `$table` WRITE;\n");
            
            $columns = array_keys($rows[0]);
            $columns_str = '`' . implode('`, `', $columns) . '`';
            
            foreach ($rows as $row) {
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $pdo->quote($value);
                    }
                }
                $values_str = implode(', ', $values);
                fwrite($handle, "INSERT INTO `$table` ($columns_str) VALUES ($values_str);\n");
            }
            
            fwrite($handle, "UNLOCK TABLES;\n\n");
        }
    }
    
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);
    
    // Удаляем старые бэкапы (оставляем только последние N)
    $backups = glob($backup_dir . '/' . $backup_prefix . '*.sql');
    if (count($backups) > $max_backups) {
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        $to_delete = array_slice($backups, 0, count($backups) - $max_backups);
        foreach ($to_delete as $file) {
            unlink($file);
        }
    }
    
    $file_size = filesize($backup_file);
    $file_size_mb = round($file_size / 1024 / 1024, 2);
    
    // Вывод результата
    if (php_sapi_name() === 'cli') {
        echo "Резервная копия успешно создана!\n";
        echo "Файл: $backup_file\n";
        echo "Размер: $file_size_mb MB\n";
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Резервная копия БД</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class='success'>
        <h2>✓ Резервная копия успешно создана!</h2>
        <p><strong>Файл:</strong> " . basename($backup_file) . "</p>
        <p><strong>Размер:</strong> $file_size_mb MB</p>
        <p><strong>Дата:</strong> " . date('Y-m-d H:i:s') . "</p>
    </div>
    <div class='info'>
        <p><a href='../admin/index.php'>← Вернуться в админ-панель</a></p>
        <p><small>Файл сохранен в: database/backups/</small></p>
    </div>
</body>
</html>";
    }
    
} catch (Exception $e) {
    $error = "Ошибка при создании резервной копии: " . $e->getMessage();
    error_log($error);
    
    if (php_sapi_name() === 'cli') {
        echo $error . "\n";
    } else {
        die("<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>$error</div>");
    }
}
?>

