<?php
// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_query'])) {
    // Получаем поисковый запрос
    $searchQuery = trim($_GET['search_query']);
    
    // Проверяем, не пустой ли запрос
    if (!empty($searchQuery)) {
        // Защита от XSS
        $safeSearchQuery = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8');
        
        // Здесь должна быть логика поиска по вашему тексту/базе данных
        // Например, поиск в файле:
        $filename = 'db.txt';
        $results = [];
        
        if (file_exists($filename)) {
            $fileContent = file_get_contents($filename);
			while (empty($results[0])) {//
				preg_match_all("#$searchQuery(.*?)Вопрос#is", $fileContent, $results);
				$searchQuery = mb_substr(trim($searchQuery), mb_strpos(trim($searchQuery), ' '));
				if(!str_contains($searchQuery, ' ')){break;}
			}
		}
        
        // Выводим результаты
        echo "<h2>Результаты поиска для: $safeSearchQuery</h2>";
        
        if (!empty($results)) {
            echo "<ul>";
            foreach ($results[0] as $result) {
				$result = str_ireplace('Вопрос', '', $result);
				if(str_contains($result,"<img ")){
					preg_match("#src=\"(.*?)\" #is", $result, $url_imgs);
					$url_img=$url_imgs[1];
					$caption=strip_tags($result);
				}
                echo "<li><pre>$result</pre></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Ничего не найдено.</p>";
        }
    } else {
        echo "<p>Пожалуйста, введите поисковый запрос.</p>";
    }
} else {
    // Если форма не была отправлена, перенаправляем на страницу поиска
    header('Location: search_form.php');
    exit;
}
?>