<?php
session_start();
if(!isset($_SESSION['user_id'])) die('Чтобы посмотреть историю заявок, надо войти в аккаунт.');
include('db.php');

// Код изменения отзыва
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int)$_SESSION['user_id'];
    $con->query("UPDATE users SET review='$review' WHERE id='$user_id'");
    echo '<div style="color:green; padding:10px; background:#e6ffe6; margin-bottom:10px;">✓ Отзыв оставлен</div>';
}

// Код истории заявок
$user_id = (int)$_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if(!$query) die('query error: ' . $con->error); 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет - история заявок</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-home {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-home:hover {
            background-color: #0056b3;
        }
        .request {
            border: 1px solid #ddd;
            margin: 15px 0;
            padding: 15px;
            border-radius: 5px;
            background-color: #fafafa;
        }
        .request h2 {
            margin-top: 0;
            color: #333;
        }
        .review-form {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        input[type="text"] {
            width: 70%;
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-home"> На главную</a>
        
        <h1>История заявок</h1>
        
        <?php
        $i = 0;
        if($query->num_rows == 0) {
            echo '<p style="text-align:center; color:#666;">У вас пока нет заявок.</p>';
        }
        while($request = $query->fetch_assoc()) {
            $i++; 
            echo '
            <div class="request">
                <h2>Заявка ' . $i . '</h2>
                <b>Дата: </b>' . htmlspecialchars($request['date']) . '<br>
                <b>Вид услуги: </b>' . htmlspecialchars($request['curses']) . '<br>
                <b>Тип оплаты: </b>' . htmlspecialchars($request['payment']) . '<br><br>
                <b>Статус: </b>' . htmlspecialchars($request['status']) . '<br>';
                
            if($request['status'] === 'Обучение завершено') {
                echo '
                <div class="review-form">
                    <form action="" method="POST">
                        <input type="text" name="review" placeholder="Отзыв об услуге" value="' . htmlspecialchars($request['review']) . '">
                        <button type="submit"> Оставить отзыв</button>
                    </form>
                </div>';
            }
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>