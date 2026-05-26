<?php
session_start();

// Выход из системы
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Проверяем, установлен ли ключ admin в сессии
$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Водить.РФ - дополнительное образование</title>
  <style>
    /* Основные цвета: синий и серебристый (как на странице регистрации) */
    :root {
      --blue-dark: #4CCFE0;
      --blue-medium: #2ca094;
      --blue-light: #4a7bcb;
      --silver: #c0c0c0;
      --silver-light: #e0e0e0;
      --white: #ffffff;
    }

    body {
      font-family: 'Rubik Bold','Rubik Medium','Rubik', 'Rubik Light', sans-serif;
      background: linear-gradient(135deg, var(--blue-dark) 0%, var(--blue-medium) 100%);
      margin: 0;
      padding: 0;
      color: var(--white);
      min-height: 100vh;
    }

    /* Шапка сайта */
    .header {
      background: #007BFF;
      padding: 15px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .logo {
      color: var(--silver);
      font-size: 24px;
      font-weight: bold;
      text-decoration: none;
      text-shadow: 0 0 10px rgba(192, 192, 192, 0.5);
      transition: all 0.3s ease;
    }

    .logo:hover {
      color: var(--silver-light);
      text-shadow: 0 0 15px rgba(192, 192, 192, 0.8);
    }

    .nav-buttons a {
      margin-left: 15px;
      padding: 10px 20px;
      border: 2px solid var(--silver);
      border-radius: 25px;
      color: var(--silver);
      text-decoration: none;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .nav-buttons a:hover {
      background-color: var(--silver);
      color: var(--blue-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Слайдер */
    .slideshow-container {
      max-width: 1000px;
      position: relative;
      margin: 40px auto;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .mySlides {
      display: none;
    }

    .fade {
      animation: fadeIn 1.5s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0.4; }
      to { opacity: 1; }
    }

    .mySlides img {
      width: 100%;
      height: 500px;
      object-fit: cover;
    }

    .text {
      position: absolute;
      bottom: 20px;
      left: 20px;
      background: #FFFFFF;
      padding: 10px 20px;
      border-radius: 5px;
      font-size: 20px;
      font-weight: bold;
      color: var(--silver);
    }

    /* Стрелки */
    .prev, .next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: #FFFFFF;
      color: var(--silver);
      border: none;
      cursor: pointer;
      padding: 15px 20px;
      font-size: 18px;
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .prev {
      left: 10px;
    }

    .next {
      right: 10px;
    }

    .prev:hover, .next:hover {
      background-color: var(--silver);
      color: var(--blue-dark);
      transform: translateY(-50%) scale(1.1);
    }

    /* Точки навигации */
    .dot-container {
      text-align: center;
      padding: 20px 0;
    }

    .dot {
      cursor: pointer;
      height: 15px;
      width: 15px;
      margin: 0 5px;
      background-color: #bbb;
      border-radius: 50%;
      display: inline-block;
      transition: background-color 0.3s ease;
    }

    .dot.active, .dot:hover {
      background-color: var(--silver);
    }

    /* Адаптивность */
    @media (max-width: 768px) {
      .nav {
        flex-direction: column;
        gap: 15px;
      }
      
      .mySlides img {
        height: 300px;
      }
      
      .text {
        font-size: 14px;
        bottom: 10px;
        left: 10px;
      }
      
      .prev, .next {
        padding: 8px 12px;
        font-size: 14px;
      }
    }
  </style>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
<!-- Шапка сайта -->
<header class="header">
  <div class="nav">
    <a href="index.php" class="logo">Водить.РФ</a>

    <!-- Кнопки навигации -->
    <div class="nav-buttons">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="btn-login">Войти</a>
        <a href="register.php" class="btn-register">Регистрация</a>
      <?php elseif ($is_admin): ?>
        <a href="admin.php" class="btn-admin">Панель администратора</a>
        <a href="?logout=1" class="btn-exit">Выход</a>
      <?php elseif (isset($_SESSION['user_id'])): ?>
        <a href="history.php" class="btn-lk">Мои заявки</a>
        <a href="create.php" class="btn-create">Новая заявка</a>
        <a href="?logout=1" class="btn-exit">Выход</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- Слайдер с картинками -->
<div class="slideshow-container">
  <!-- Слайды -->
  <div class="mySlides fade">
    <img src="https://buran-rus.ru/assets/images/products/13434/acc923bd7593d5e7d6bd02739f2f974c.jpg" alt="Слайд 1">
    <div class="text">Вождение катеров</div>
  </div>

  <div class="mySlides fade">
    <img src="https://www.discovercruise.ru/data/0.882041001318835887.jpg" alt="Слайд 2">
    <div class="text">Вождение круизных лайнеров</div>
  </div>

  

  <div class="mySlides fade">
    <img src="https://www.azimutyachts.ru/prev1200x800/i/goods/1500-1-Azimut-58-Nino.jpg" alt="Слайд 4">
    <div class="text">Вождение яхт</div>
  </div>

  <a class="prev" onclick="plusSlides(-1)">❮</a>
  <a class="next" onclick="plusSlides(1)">❯</a>
</div>

<!-- Точки навигации -->
<div class="dot-container">
  <span class="dot" onclick="currentSlide(1)"></span>
  <span class="dot" onclick="currentSlide(2)"></span>
  <span class="dot" onclick="currentSlide(3)"></span>
  <span class="dot" onclick="currentSlide(4)"></span>
</div>

<!-- Основной контент -->
<section style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
  <h2 style="text-align: center; color: var(--silver); margin-bottom: 30px;">Почему выбирают нас?</h2>
  
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
    <div style="background: #007BFF; padding: 25px; border-radius: 10px; text-align: center;">
      <h3 style="color: var(--silver-light);">Опытные инструкторы</h3>
      <p style="color: var(--silver); line-height: 1.5;">Все наши инструкторы имеют многолетний опыт и международные сертификаты.</p>
    </div>
    
    <div style="background: #007BFF; padding: 25px; border-radius: 10px; text-align: center;">
      <h3 style="color: var(--silver-light);">Современное оборудование</h3>
      <p style="color: var(--silver); line-height: 1.5;">Мы используем только новые и проверенные суда для обучения.</p>
    </div>
    
    <div style="background: #007BFF; padding: 25px; border-radius: 10px; text-align: center;">
      <h3 style="color: var(--silver-light);">Гибкий график</h3>
      <p style="color: var(--silver); line-height: 1.5;">Подберём удобное время для занятий под ваш график.</p>
    </div>
  </div>
</section>

<script>
// JavaScript для управления слайдером
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");

  if (n > slides.length) { slideIndex = 1 }
  if (n < 1) { slideIndex = slides.length }

  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }

  slides[slideIndex-1].style.display = "block";
  dots[slideIndex-1].className += " active";
}

// Автоматическое переключение слайдов каждые 3 секунды
let slideInterval = setInterval(function() {
  plusSlides(1);
}, 3000);

// Останавливаем автоматическое переключение при наведении на слайдер
const slideshowContainer = document.querySelector('.slideshow-container');
if (slideshowContainer) {
  slideshowContainer.addEventListener('mouseenter', function() {
    clearInterval(slideInterval);
  });
  
  slideshowContainer.addEventListener('mouseleave', function() {
    slideInterval = setInterval(function() {
      plusSlides(1);
    }, 3000);
  });
}
</script>
</body>
</html>