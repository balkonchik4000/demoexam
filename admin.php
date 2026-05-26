<?php
include('db.php');
session_start();
if (!isset($_SESSION['admin']) || !$_SESSION['admin']) die('Чтобы посмотреть панель администратора, надо войти в его аккаунт.');

// Обработка изменения статуса заявки
$status_updated = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $con->real_escape_string($_POST['status']);
    $query = $con->query("UPDATE request SET status='$status' WHERE id=$request_id");
    if (!$query) {
        die('update error: ' . $con->error);
    } else {
        $status_updated = true;
    }
}

// Получение всех заявок с данными пользователей
$query = $con->query("SELECT request.*, users.login, users.fullname 
                      FROM request 
                      INNER JOIN users ON request.user_id = users.id
                      ORDER BY request.date DESC");
if (!$query) die('query error: ' . $con->error);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель Администратора - Водить.РФ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #5885b9 0%, #2ca094 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Шапка */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
            animation: titleGlow 2s ease-in-out infinite;
        }

        @keyframes titleGlow {
            0%, 100% {
                text-shadow: 0 0 0px rgba(88, 133, 185, 0);
            }
            50% {
                text-shadow: 0 0 10px rgba(88, 133, 185, 0.3);
            }
        }

        .subtitle {
            color: #666;
            font-size: 16px;
        }

        /* Кнопки навигации */
        .nav-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            justify-content: center;
        }

        .btn-nav {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #5885b9 0%, #2ca094 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .btn-nav:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #2ca094 0%, #5885b9 100%);
        }

        .btn-nav:active {
            transform: translateY(0);
        }

        /* Статистика */
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat-card {
            flex: 1;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            animation: fadeInScale 0.5s ease-out;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #5885b9;
        }

        .stat-label {
            color: #666;
            margin-top: 5px;
        }

        /* Карточка заявки */
        .request-card {
            background: linear-gradient(135deg, #f9f9f9 0%, #ffffff 100%);
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            animation: cardAppear 0.5s ease-out;
            animation-fill-mode: both;
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #5885b9;
        }

        /* Задержки для карточек */
        .request-card:nth-child(1) { animation-delay: 0.1s; }
        .request-card:nth-child(2) { animation-delay: 0.2s; }
        .request-card:nth-child(3) { animation-delay: 0.3s; }
        .request-card:nth-child(4) { animation-delay: 0.4s; }
        .request-card:nth-child(5) { animation-delay: 0.5s; }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-title {
            font-size: 20px;
            color: #333;
        }

        .card-number {
            background: linear-gradient(135deg, #5885b9 0%, #2ca094 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 10px;
            background: rgba(88, 133, 185, 0.05);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: rgba(88, 133, 185, 0.1);
            transform: translateX(5px);
        }

        .info-label {
            font-weight: bold;
            color: #5885b9;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            color: #333;
            font-size: 16px;
            margin-top: 5px;
            word-break: break-word;
        }

        /* Форма статуса */
        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #e0e0e0;
        }

        .status-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        .status-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            cursor: pointer;
        }

        .status-select:focus {
            outline: none;
            border-color: #5885b9;
            box-shadow: 0 0 0 3px rgba(88, 133, 185, 0.2);
        }

        .status-select:hover {
            border-color: #2ca094;
        }

        /* Статусные цвета */
        .status-select option[value="Новая"] { color: #ffc107; }
        .status-select option[value="Идет обучение"] { color: #17a2b8; }
        .status-select option[value="Обучение завершено"] { color: #28a745; }

        .btn-save {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-save::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-save:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-save:active {
            transform: translateY(0);
        }

        /* Уведомление об успехе */
        .success-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            animation: slideInRight 0.5s ease-out, fadeOut 0.5s ease-out 2.5s forwards;
            z-index: 1000;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Пустое состояние */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f9f9f9;
            border-radius: 15px;
        }

        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
            
            .stats {
                flex-direction: column;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-buttons">
            <a href="index.php" class="btn-nav">🏠 Главная</a>
            <a href="?logout=1" class="btn-nav" onclick="return confirm('Выйти из аккаунта?')">🚪 Выход</a>
        </div>

        <div class="header">
            <h1>👨‍💼 Панель администратора</h1>
            <p class="subtitle">Управление заявками пользователей</p>
        </div>

        <?php
        // Подсчет статистики
        $total_requests = $query->num_rows;
        $new_requests = 0;
        $in_progress = 0;
        $completed = 0;
        
        // Временное сохранение результатов для подсчета
        $requests_data = [];
        while ($row = $query->fetch_assoc()) {
            $requests_data[] = $row;
            switch ($row['status']) {
                case 'Новая': $new_requests++; break;
                case 'Идет обучение': $in_progress++; break;
                case 'Обучение завершено': $completed++; break;
            }
        }
        ?>

        <!-- Статистика -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total_requests ?></div>
                <div class="stat-label"> Всего заявок</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ffc107;"><?= $new_requests ?></div>
                <div class="stat-label"> Новые</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #17a2b8;"><?= $in_progress ?></div>
                <div class="stat-label"> В обучении</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;"><?= $completed ?></div>
                <div class="stat-label">✅ Завершено</div>
            </div>
        </div>

        <?php if (empty($requests_data)): ?>
            <div class="empty-state">
                <h3>📭 Пока нет заявок</h3>
                <p>Когда пользователи оставят заявки, они появятся здесь</p>
            </div>
        <?php else: ?>
            <?php foreach ($requests_data as $index => $request): ?>
                <div class="request-card">
                    <div class="card-header">
                        <h2 class="card-title">
                            👤 <?= htmlspecialchars($request['login']) ?>
                        </h2>
                        <span class="card-number">Заявка №<?= $index + 1 ?></span>
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label"> ФИО</div>
                            <div class="info-value"><?= htmlspecialchars($request['fullname']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"> Дата</div>
                            <div class="info-value"><?= htmlspecialchars($request['date']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"> Услуга</div>
                            <div class="info-value"><?= htmlspecialchars($request['curses']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"> Оплата</div>
                            <div class="info-value"><?= htmlspecialchars($request['payment']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label"> Комментарий</div>
                            <div class="info-value"><?= htmlspecialchars($request['review']) ?: '—' ?></div>
                        </div>
                    </div>

                    <div class="status-form">
                        <form action="" method="POST" class="status-update-form">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                            <label>📊 Изменить статус</label>
                            <select name="status" class="status-select">
                                <option <?= $request['status'] == 'Новая' ? 'selected' : '' ?> value="Новая">🆕 Новая</option>
                                <option <?= $request['status'] == 'Идет обучение' ? 'selected' : '' ?> value="Идет обучение">📖 Идет обучение</option>
                                <option <?= $request['status'] == 'Обучение завершено' ? 'selected' : '' ?> value="Обучение завершено">✅ Обучение завершено</option>
                            </select>
                            <button type="submit" class="btn-save">💾 Сохранить изменения</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        // Уведомление об успешном обновлении статуса
        <?php if ($status_updated): ?>
            const notification = document.createElement('div');
            notification.className = 'success-notification';
            notification.innerHTML = '✅ Статус заявки успешно обновлен!';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        <?php endif; ?>

        // Анимация при отправке формы
        const forms = document.querySelectorAll('.status-update-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const button = this.querySelector('.btn-save');
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Сохранение...';
                button.style.opacity = '0.7';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.opacity = '1';
                }, 2000);
            });
        });

        // Плавное появление карточек при прокрутке
        const cards = document.querySelectorAll('.request-card');
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateX(0)';
                }
            });
        }, observerOptions);
        
        cards.forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>