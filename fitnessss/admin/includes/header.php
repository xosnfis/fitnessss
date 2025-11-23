<!DOCTYPE html>
<html lang="ru" class="smooth-scroll">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Админ-панель фитнес-центра">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Админ-панель - Фитнес-центр</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header {
            background: var(--gradient-secondary);
            box-shadow: var(--shadow-lg);
            padding: 1rem 0;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .admin-breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .admin-breadcrumb .breadcrumb-item {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .admin-breadcrumb .breadcrumb-item.active {
            color: #FFFFFF;
        }
        
        .admin-breadcrumb .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: color var(--transition-base);
        }
        
        .admin-breadcrumb .breadcrumb-item a:hover {
            color: #FFFFFF;
        }
        
        .admin-nav-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .admin-page-title {
            color: var(--text-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .admin-page-title i {
            color: var(--primary-color);
            font-size: 1.2em;
        }
        
        .admin-stat-card {
            background: var(--gradient-primary);
            color: #FFFFFF;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-base);
            text-align: center;
        }
        
        .admin-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .admin-stat-card h3 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0.5rem 0;
            color: #FFFFFF;
        }
        
        .admin-stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .admin-stat-card.secondary {
            background: var(--gradient-secondary);
        }
        
        .admin-stat-card.success {
            background: linear-gradient(135deg, var(--success-color), #07C8A0);
        }
        
        .admin-stat-card.warning {
            background: linear-gradient(135deg, #FFC107, #FFB300);
        }
        
        /* Улучшение таблиц в админ-панели */
        .admin-header + main .table {
            margin-bottom: 2rem;
        }
        
        .admin-header + main .table thead th {
            padding: 1.25rem 1rem;
            font-weight: 700;
            font-size: 1rem;
            vertical-align: middle;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .admin-header + main .table tbody td {
            padding: 1.5rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .admin-header + main .table tbody tr:hover {
            background-color: rgba(255, 107, 53, 0.05);
        }
        
        .admin-header + main .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .admin-header + main .table .btn-sm {
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .admin-header + main .table-responsive {
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        
        .admin-header + main .table-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .admin-header + main .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        /* Увеличение отступов для форм в таблицах */
        .admin-header + main .table .form-select-sm {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            font-size: 0.875rem;
            min-width: 150px;
        }
        
        /* Отступы между кнопками действий */
        .admin-header + main .table td:last-child {
            white-space: nowrap;
        }
        
        .admin-header + main .table td:last-child .btn {
            margin-right: 0.5rem;
        }
        
        .admin-header + main .table td:last-child .btn:last-child {
            margin-right: 0;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <nav aria-label="breadcrumb" class="admin-breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php">
                                <i class="fas fa-home me-1"></i>Админ-панель
                            </a>
                        </li>
                        <?php if (isset($pageTitle) && $pageTitle !== 'Админ-панель'): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo htmlspecialchars($pageTitle); ?>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>
                <div class="admin-nav-buttons">
                    <?php if (isset($pageTitle) && $pageTitle !== 'Админ-панель'): ?>
                        <a href="index.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Назад в админ-панель
                        </a>
                    <?php endif; ?>
                    <a href="../index.php" class="btn btn-light btn-sm">
                        <i class="fas fa-globe me-1"></i>На сайт
                    </a>
                    <a href="../logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>Выйти
                    </a>
                </div>
            </div>
        </div>
    </div>
    <main class="container my-4 flex-grow-1">

