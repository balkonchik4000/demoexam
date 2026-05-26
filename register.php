<?php
session_start();

// Если пользователь уже авторизован, перенаправляем
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';
$success = false;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    
    $form_data = compact('login', 'fullname', 'phone', 'email');
    
    // Валидация данных
    $errors = [];
    
    if (empty($login)) {
        $errors[] = 'Логин обязателен для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors[] = 'Логин должен содержать только латиницу и цифры, минимум 6 символов';
    }
    
    if (empty($password)) {
        $errors[] = 'Пароль обязателен для заполнения';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать минимум 8 символов';
    }
    
    if (empty($fullname)) {
        $errors[] = 'ФИО обязательно для заполнения';
    } elseif (strlen($fullname) < 5) {
        $errors[] = 'Введите полное ФИО';
    }
    
    if (empty($phone)) {
        $errors[] = 'Телефон обязателен для заполнения';
    } elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    }
    
    if (empty($email)) {
        $errors[] = 'Email обязателен для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email';
    }
    
    if (empty($errors)) {
        include('db.php');
        
        // Проверка на существование логина
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = true;
            $error_message = 'Пользователь с таким логином уже существует';
        } else {
            // Проверка на существование email
            $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = true;
                $error_message = 'Пользователь с таким email уже существует';
            } else {
                // Рекомендуется хешировать пароль
                // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Для совместимости с существующей системой пока оставляем как есть
                
                $stmt = $con->prepare("INSERT INTO users (login, password, fullname, phone, email) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $login, $password, $fullname, $phone, $email);
                
                if ($stmt->execute()) {
                    $success = true;
                    // Перенаправление через 2 секунды
                    header('refresh:2;url=login.php');
                } else {
                    $error = true;
                    $error_message = 'Ошибка при регистрации: ' . $con->error;
                }
                $stmt->close();
            }
        }
        $stmt->close();
    } else {
        $error = true;
        $error_message = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Водить.РФ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #4CCFE0 0%, #007BFF 100%);
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Анимированные круги на фоне */
        .circle {
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 20s infinite linear;
            z-index: 0;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
            }
        }

        /* Генерация кругов через JavaScript */

        .container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            animation: slideInUp 0.6s ease-out;
            position: relative;
            z-index: 1;
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

        /* Логотип */
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 32px;
            background: linear-gradient(135deg, #5885b9 0%, #2ca094 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: titleGlow 2s ease-in-out infinite;
        }

        @keyframes titleGlow {
            0%, 100% {
                text-shadow: 0 0 0px rgba(88, 133, 185, 0);
            }
            50% {
                text-shadow: 0 0 15px rgba(88, 133, 185, 0.3);
            }
        }

        .logo p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        /* Заголовок формы */
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .form-header p {
            color: #888;
            font-size: 14px;
        }

        /* Сообщение об ошибке */
        .error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #dc3545;
            animation: shakeError 0.5s ease-in-out;
        }

        /* Сообщение об успехе */
        .success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #28a745;
            animation: slideInRight 0.5s ease-out;
        }

        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Стили формы */
        .form-group {
            margin-bottom: 20px;
            position: relative;
            animation: fadeInUp 0.5s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.05s; }
        .form-group:nth-child(2) { animation-delay: 0.1s; }
        .form-group:nth-child(3) { animation-delay: 0.15s; }
        .form-group:nth-child(4) { animation-delay: 0.2s; }
        .form-group:nth-child(5) { animation-delay: 0.25s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            transition: all 0.3s ease;
        }

        .form-group label i, .form-group label span {
            margin-right: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #5885b9;
            box-shadow: 0 0 0 4px rgba(88, 133, 185, 0.2);
            transform: scale(1.02);
            background: white;
        }

        .form-group input:hover {
            border-color: #2ca094;
            background: white;
        }

        .form-group input.error {
            border-color: #dc3545;
            background: #fff5f5;
        }

        /* Подсказки */
        .hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
            display: block;
        }

        /* Кнопка регистрации */
        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
            animation: fadeInUp 0.5s ease-out 0.3s both;
        }

        .btn-register::before {
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

        .btn-register:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Ссылки */
        .form-footer {
            margin-top: 25px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            animation: fadeInUp 0.5s ease-out 0.35s both;
        }

        .form-footer p {
            color: #666;
            margin-bottom: 10px;
        }

        .login-link {
            color: #5885b9;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .login-link:hover {
            color: #2ca094;
            transform: translateX(5px);
        }

        .back-home {
            display: inline-block;
            margin-top: 15px;
            color: #888;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            color: #5885b9;
        }

        /* Адаптивность */
        @media (max-width: 550px) {
            .container {
                padding: 25px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
            
            .btn-register {
                padding: 12px;
                font-size: 16px;
            }
            
            .form-group input {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>⚓ Водить.РФ</h1>
            <p>Обучение судовождению</p>
        </div>

        <div class="form-header">
            <h2>Создание аккаунта</h2>
            <p>Заполните форму для регистрации</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                ✅ Регистрация успешно завершена!<br>
                <small>Перенаправление на страницу входа...</small>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="fullname">
                    <span>👤</span> ФИО
                </label>
                <input type="text" id="fullname" name="fullname" 
                       value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>"
                       placeholder="Иванов Иван Иванович" required>
                <span class="hint">Ваше полное имя</span>
            </div>

            <div class="form-group">
                <label for="phone">
                    <span>📱</span> Телефон
                </label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>"
                       placeholder="+7(XXX)XXX-XX-XX" 
                       pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" required>
                <span class="hint">Формат: +7(XXX)XXX-XX-XX</span>
            </div>

            <div class="form-group">
                <label for="email">
                    <span>📧</span> Email
                </label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                       placeholder="example@mail.com" required>
                <span class="hint">На этот адрес будут приходить уведомления</span>
            </div>

            <div class="form-group">
                <label for="login">
                    <span>🔑</span> Логин
                </label>
                <input type="text" id="login" name="login" 
                       value="<?php echo htmlspecialchars($form_data['login'] ?? ''); ?>"
                       placeholder="ivan123" 
                       pattern="[a-zA-Z0-9]{6,}" required>
                <span class="hint">Только латиница и цифры, минимум 6 символов</span>
            </div>

            <div class="form-group">
                <label for="password">
                    <span>🔒</span> Пароль
                </label>
                <input type="password" id="password" name="password" 
                       placeholder="Минимум 8 символов" minlength="8" required>
                <span class="hint" id="passwordHint">Минимум 8 символов</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    <span>✅</span>Дата рождения
                </label>
                <input id="date" type="date-local" name="date" required> 
                       
                <span class="hint" id="confirmHint"></span>
            </div>

            <button type="submit" class="btn-register" id="submitBtn">
                🚢 Зарегистрироваться
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Уже есть аккаунт? <a href="login.php" class="login-link">Войти →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
        </div>
    </div>

    <script>
        // Клиентская валидация
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const confirmHint = document.getElementById('confirmHint');
        const passwordHint = document.getElementById('passwordHint');
        const submitBtn = document.getElementById('submitBtn');
        
        // Проверка пароля в реальном времени
        if (password) {
            password.addEventListener('input', function() {
                const value = this.value;
                if (value.length >= 8) {
                    passwordHint.innerHTML = '✅ Пароль надежный';
                    passwordHint.style.color = '#28a745';
                } else {
                    passwordHint.innerHTML = '⚠️ Минимум 8 символов';
                    passwordHint.style.color = '#dc3545';
                }
                
                if (confirmPassword.value) {
                    checkPasswordsMatch();
                }
            });
        }
        
        // Проверка совпадения паролей
        function checkPasswordsMatch() {
            if (password.value === confirmPassword.value && password.value.length >= 8) {
                confirmHint.innerHTML = '✅ Пароли совпадают';
                confirmHint.style.color = '#28a745';
                return true;
            } else if (confirmPassword.value.length > 0) {
                confirmHint.innerHTML = '❌ Пароли не совпадают';
                confirmHint.style.color = '#dc3545';
                return false;
            }
            return false;
        }
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkPasswordsMatch);
        }
        
        // Валидация телефона
        const phone = document.getElementById('phone');
        if (phone) {
            phone.addEventListener('input', function(e) {
                let value = this.value;
                // Автоматическое форматирование
                if (value.length === 1 && value !== '+') {
                    this.value = '+' + value;
                }
            });
        }
        
        // Валидация перед отправкой
        if (form) {
            form.addEventListener('submit', function(e) {
                // Проверка паролей
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    showInlineError('Пароли не совпадают');
                    confirmPassword.style.borderColor = '#dc3545';
                    return false;
                }
                
                if (password.value.length < 8) {
                    e.preventDefault();
                    showInlineError('Пароль должен содержать минимум 8 символов');
                    password.style.borderColor = '#dc3545';
                    return false;
                }
                
                // Проверка телефона
                const phonePattern = /^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
                if (!phonePattern.test(phone.value)) {
                    e.preventDefault();
                    showInlineError('Введите телефон в формате +7(XXX)XXX-XX-XX');
                    phone.style.borderColor = '#dc3545';
                    return false;
                }
                
                // Проверка логина
                const loginPattern = /^[a-zA-Z0-9]{6,}$/;
                const login = document.getElementById('login');
                if (!loginPattern.test(login.value)) {
                    e.preventDefault();
                    showInlineError('Логин должен содержать только латиницу и цифры, минимум 6 символов');
                    login.style.borderColor = '#dc3545';
                    return false;
                }
                
                // Анимация кнопки
                submitBtn.innerHTML = '⏳ Регистрация...';
                submitBtn.disabled = true;
            });
        }
        
        // Функция показа ошибки
        function showInlineError(message) {
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const formHeader = document.querySelector('.form-header');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `⚠️ ${message}`;
            formHeader.insertAdjacentElement('afterend', errorDiv);
            
            // Убираем ошибку через 3 секунды
            setTimeout(() => {
                errorDiv.style.opacity = '0';
                setTimeout(() => errorDiv.remove(), 300);
            }, 3000);
        }
        
        // Убираем красную рамку при вводе
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '#e0e0e0';
            });
            
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(5px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });
        
        // Создание анимированных кругов на фоне
        function createCircles() {
            for (let i = 0; i < 10; i++) {
                const circle = document.createElement('div');
                circle.className = 'circle';
                const size = Math.random() * 100 + 50;
                circle.style.width = size + 'px';
                circle.style.height = size + 'px';
                circle.style.left = Math.random() * 100 + '%';
                circle.style.bottom = '-' + size + 'px';
                circle.style.animationDuration = Math.random() * 15 + 10 + 's';
                circle.style.animationDelay = Math.random() * 5 + 's';
                document.body.appendChild(circle);
            }
        }
        
        createCircles();
    </script>
</body>
</html>