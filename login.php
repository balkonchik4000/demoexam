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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        include('db.php');
        
        // Используем подготовленные выражения для защиты от SQL инъекций
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = true;
            $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();
            
            // Проверка пароля (рекомендуется использовать password_hash() при регистрации)
            if ($password !== $user['password']) {
                $error = true;
                $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];
                
                // Проверка на администратора
                if ($user['login'] == 'Admin26') {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Водить.РФ</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Анимированные волны на фоне */
        .wave {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,208C384,203,480,181,576,181.3C672,181,768,203,864,208C960,213,1056,203,1152,186.7C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') repeat-x;
            background-size: cover;
            animation: waveMove 10s linear infinite;
            z-index: 0;
        }

        @keyframes waveMove {
            0% { background-position-x: 0; }
            100% { background-position-x: 1440px; }
        }

        .container {
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            animation: slideInUp 0.6s ease-out;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Стили формы */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            transition: all 0.3s ease;
        }

        .form-group label i {
            margin-right: 8px;
            color: #5885b9;
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

        /* Кнопка входа */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #28A745 0%, #4CCFE0 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
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

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(88, 133, 185, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Дополнительные ссылки */
        .form-footer {
            margin-top: 25px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .form-footer p {
            color: #666;
            margin-bottom: 10px;
        }

        .register-link {
            color: #2ca094;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .register-link:hover {
            color: #5885b9;
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

        /* Анимация для инпутов */
        .form-group {
            animation: fadeInUp 0.5s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .btn-login { animation: fadeInUp 0.5s ease-out 0.3s both; }
        .form-footer { animation: fadeInUp 0.5s ease-out 0.4s both; }

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

        /* Адаптивность */
        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
            
            .btn-login {
                padding: 12px;
                font-size: 16px;
            }
        }

        /* Стили для иконок (если не подключали Font Awesome) */
        .icon {
            display: inline-block;
            width: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wave"></div>
    
    <div class="container">
        <div class="logo">
            <h1>⚓ Водить.РФ</h1>
            <p>Обучение судовождению</p>
        </div>

        <div class="form-header">
            <h2>Добро пожаловать!</h2>
            <p>Войдите в свой аккаунт</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <span>⚠️</span>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="login">
                    <span class="icon">👤</span> Логин
                </label>
                <input type="text" id="login" name="login" 
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                       placeholder="Введите ваш логин" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">
                    <span class="icon">🔒</span> Пароль
                </label>
                <input type="password" id="password" name="password" 
                       placeholder="Введите пароль" required>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                <span class="icon">🚀</span> Войти
            </button>
        </form>

        <div class="form-footer">
            <p>Нет аккаунта? <a href="register.php" class="register-link">Зарегистрироваться →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
        </div>
    </div>

    <script>
        // Анимация при отправке формы
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const login = document.getElementById('login').value.trim();
                const password = document.getElementById('password').value;
                
                if (!login || !password) {
                    e.preventDefault();
                    showError('Пожалуйста, заполните все поля');
                    return;
                }
                
                // Добавляем анимацию загрузки
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="icon">⏳</span> Вход...';
                submitBtn.style.opacity = '0.7';
                submitBtn.disabled = true;
                
                // Если форма валидна, она отправится автоматически
                setTimeout(() => {
                    // Эта функция выполнится только если форма не отправилась
                    submitBtn.innerHTML = originalText;
                    submitBtn.style.opacity = '1';
                    submitBtn.disabled = false;
                }, 3000);
            });
        }
        
        // Функция показа ошибки (клиентская валидация)
        function showError(message) {
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<span>⚠️</span> ${message}`;
            
            const formHeader = document.querySelector('.form-header');
            formHeader.insertAdjacentElement('afterend', errorDiv);
            
            // Анимация встряхивания контейнера
            const container = document.querySelector('.container');
            container.style.animation = 'shakeError 0.5s ease-in-out';
            setTimeout(() => {
                container.style.animation = '';
            }, 500);
        }
        
        // Добавляем эффект при наведении на инпуты
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(5px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });
        
        // Сохраняем логин в localStorage (для удобства, опционально)
        const savedLogin = localStorage.getItem('savedLogin');
        if (savedLogin && !document.getElementById('login').value) {
            document.getElementById('login').value = savedLogin;
        }
        
        form.addEventListener('submit', function() {
            const login = document.getElementById('login').value;
            localStorage.setItem('savedLogin', login);
        });
    </script>
</body>
</html>