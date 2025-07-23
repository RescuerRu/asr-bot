<?php
function check_string($stroka, $key) {  
    foreach($key as $k) {  
        if(strpos($stroka, $k) !== false) {  
            echo "$k есть в тексте";  
        } else {  
            echo "$k нет в тексте";  
        }  
    }  
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Поиск по тексту</title>
</head>
<body>
    <h1>Поиск по тексту</h1>
    
    <form action="search_handler.php" method="get">
        <input type="text" name="search_query" placeholder="Введите поисковый запрос">
        <button type="submit">Искать</button>
    </form>
</body>
</html>