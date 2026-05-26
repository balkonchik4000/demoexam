<?php
session_start();
if (!isset($_SESSION['user_id'])) die('Чтобы оставить заявку, надо войти в аккаунт.');

$success = false;
$error = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review = $_POST['review'];
    $date = $_POST['date'];
    $curses = $_POST['curses'];
    $payment = $_POST['payment'];
    $status = 'Новая'; // Статус устанавливается автоматически
    
    include('db.php');
    
    // Для безопасности в реальном проекте используйте подготовленные выражения (prepared statements)
    $user_id = (int)$_SESSION['user_id']; // Защита от SQL-инъекций
    $review = $con->real_escape_string($review);
    $curses = $con->real_escape_string($curses);
    $payment = $con->real_escape_string($payment);
    
    $query = $con->query("INSERT INTO request (review, date, curses, payment, user_id, status) 
                          VALUES ('$review', '$date', '$curses', '$payment', '$user_id', '$status')");
    
    if (!$query) {
        $error = true;
        $error_msg = 'Ошибка: ' . $con->error;
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание заявки - Водить.РФ</title>
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
            max-width: 550px;
            margin: 0 auto;
            background: #fff;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideInUp 0.6s ease-out;
            transform-origin: center;
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

        /* Стили для кнопок навигации */
        .nav-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
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
            flex: 1;
        }

        .btn-nav:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #2ca094 0%, #5885b9 100%);
        }

        .btn-nav:active {
            transform: translateY(0);
        }

        h1 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 28px;
        }

        /* Стили формы */
        form {
            animation: formFadeIn 0.8s ease-out 0.2s both;
        }

        @keyframes formFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            transform-origin: left;
            transition: all 0.3s ease;
        }

        form label:hover {
            color: #5885b9;
            transform: translateX(5px);
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            outline: none;
            border-color: #5885b9;
            box-shadow: 0 0 0 3px rgba(88, 133, 185, 0.2);
            transform: scale(1.02);
            background: white;
        }

        form input:hover,
        form select:hover,
        form textarea:hover {
            border-color: #2ca094;
            background: white;
        }

        form textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Кнопка отправки */
        form button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        form button::before {
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

        form button:hover::before {
            width: 300px;
            height: 300px;
        }

        form button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
            animation: buttonShake 0.5s ease;
        }

        @keyframes buttonShake {
            0%, 100% { transform: translateY(-3px) rotate(0deg); }
            25% { transform: translateY(-3px) rotate(2deg); }
            75% { transform: translateY(-3px) rotate(-2deg); }
        }

        form button:active {
            transform: translateY(0px);
        }

        /* Сообщения об успехе/ошибке */
        .success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            text-align: center;
            border: 2px solid #c3e6cb;
            animation: messageSlideIn 0.5s ease-out, successPulse 1.5s ease-in-out 0.5s;
        }

        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes successPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
            }
            50% {
                transform: scale(1.02);
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
        }

        .error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            text-align: center;
            border: 2px solid #f5c6cb;
            animation: messageSlideIn 0.5s ease-out, errorShake 0.5s ease-in-out;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .success-message a,
        .error-message a {
            color: inherit;
            font-weight: bold;
            text-decoration: underline;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .success-message a:hover,
        .error-message a:hover {
            transform: scale(1.05);
            text-decoration: none;
        }

        /* Анимация для select опций */
        select option {
            animation: optionFadeIn 0.3s ease;
        }

        @keyframes optionFadeIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Эффект загрузки для кнопки */
        form button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        form button.loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-left: 10px;
            border: 3px solid white;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Адаптивность */
        @media (max-width: 600px) {
            .container {
                padding: 25px;
                margin: 0 15px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
            
            form input,
            form select,
            form textarea {
                padding: 10px;
            }
            
            form button {
                padding: 12px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Кнопки навигации -->
        <div class="nav-buttons">
            <a href="index.php" class="btn-nav"> Главная</a>
            <a href="history.php" class="btn-nav"> История заявок</a>
        </div>
        
        <h1> Создание заявки</h1>

        <?php if ($success): ?>
            <div class="success-message">
                 Заявка успешно отправлена!<br><br>
                <a href="history.php">🔍 Перейти к истории моих заявок →</a>
                <br><br>
                 Спасибо, что выбрали нас!
            </div>
        <?php elseif ($error): ?>
            <div class="error-message">
                 Ошибка при отправке заявки: <?php echo htmlspecialchars($error_msg); ?><br>
                <a href="javascript:history.back()">◀ Попробовать снова</a>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="requestForm">
            
            <label for="curses">🚤 Название курса</label>
            <select id="curses" name="curses" required>
                <option value="Вождение катеров">Вождение катеров</option>
                <option value="Вождение круизных лайнеров">Вождение круизных лайнеров</option>
                <option value="Вождение яхт">Вождение яхт</option>
               
            </select>

            <label for="date"> Когда желаете начать обучение?</label>
            <input id="date" type="datetime-local" name="date" required>

            <label for="payment"> Способ оплаты</label>
            <select id="payment" name="payment" required>
                <option value="наличные">Наличные</option>
                <option value="перевод">Переводом по номеру</option>
                <option value="карта">Банковской картой</option>
            </select>

            <label for="review"> Дополнительная информация</label>
            <textarea id="review" name="review" placeholder="Опишите ваши пожелания или комментарий..."></textarea>
             
            <button type="submit" id="submitBtn"> Отправить заявку</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Анимация при отправке формы
        const form = document.getElementById('requestForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                // Добавляем класс загрузки на кнопку
                submitBtn.classList.add('loading');
                submitBtn.textContent = 'Отправка';
            });
        }

        // Анимация при фокусе на полях
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transition = 'all 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.style.transform = 'scale(1)';
                }
            });
        });
    </script>
</body>
</html>