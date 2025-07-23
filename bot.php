<?php

$input = file_get_contents("php://input");
$update = json_decode($input, true);       
function post($url,$refer){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,30); 
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36");
	curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result  = curl_exec($ch);
	curl_close($ch);
	return $result;
}

if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    // Защита от XSS
	$searchQuery = $update['message']['text'];
	$safeSearchQuery = htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8');
	$lines_stat = file('bot.txt');
	while(count($lines_stat) > 1000) array_shift($lines_stat);
	$lines_stat[] = $safeSearchQuery.PHP_EOL;
	file_put_contents('bot.txt', $lines_stat);
	$text = trim($searchQuery);
	if (str_contains($text, "кое направление ветра")) {$text="Как определить направление ветра";}
	if (str_contains($text, "акой ветер")) {$text="Как определить направление ветра";}
	$text = str_ireplace('ё', 'е', $text);
	$text = mb_strtolower($text);
	if ($text == "/start" || str_contains($text,"привет")) {
        $response = "Привет! Я бот - Спасатель.👨‍🚒\n\rЯ не плохо отвечаю на вопросы тестов по темам:\n\r - Тактика и оснащение ГСР.\n\r - ГСР на высоте.\n\r\n\r Ещё я могу подсказать результаты тестрования. Отправьте Ф.И.О. под которым вы проходили тест.";
    }
	else { 
		$html=post('http://asr.yzz.me/zachot.txt','http://asr.yzz.me/');
		$tops = explode("\n", $html);
		$response = " ";
		foreach($tops as $vol){
			if($vol==''){continue;}
			$vols=explode("|", $vol);
			$vols[1] = str_ireplace('ё', 'е', $vols[1]);
			if(str_contains(mb_strtolower($vols[1]), $text)){
				if (isset($vols[7])){$vols[0]=$vols[0]." тема №".$vols[7];}
				$tema='';			
				if(str_contains($vols[0],'larn')&&str_contains($vols[0],'№3')){$tema='Оснащение АСФ';}
				elseif(str_contains($vols[0],'larn')&&str_contains($vols[0],'№1')){$tema='Законы и оснащение ЛРН';}
				elseif(str_contains($vols[0],'lrn')&&str_contains($vols[0],'№3')){$tema='Контроль состава атмосферы';}
				elseif(str_contains($vols[0],'lrn')&&str_contains($vols[0],'№1')){$tema='Законы и оснащение ЛРН';}
				elseif(str_contains($vols[0],'№9')){$tema='Нормативные документы ЛРН';}
				elseif(str_contains($vols[0],'№1')){$tema='Нормативные документы АСФ';}
				elseif(str_contains($vols[0],'№2')){$tema='Устав АСФ по ведению ГСР';}
				elseif(str_contains($vols[0],'№3')){$tema='Оснащение. СИЗОД и СИЗК';}
				elseif(str_contains($vols[0],'№4')){$tema='Метеоусловия';}
				elseif(str_contains($vols[0],'№5')){$tema='Контроль состава атмосферы';}
				elseif(str_contains($vols[0],'№6')){$tema='Виды и способы связи';}
				elseif(str_contains($vols[0],'№7')){$tema='Эвакуация пострадавшего';}
				elseif(str_contains($vols[0],'№8')){$tema='Переключение в ИДА';}
				elseif(str_contains($vols[0],'№11')){$tema='Переключение в ИДА';}
				
				$vol=$vols[1]." ".$tema." - ".$vols[3];
				$response .= $vol."\n\r";
			}
			
		}
		if($response == " ") {$response = "Вы написали: " . $searchQuery . " - НЕТ такого спасателя!";}
    }
	if(str_contains($response,"НЕТ такого спасателя!")){
        
        // Здесь должна быть логика поиска по вашему тексту/базе данных
        // Например, поиск в файле:
        $fileContent=post('http://asr.yzz.me/db.txt','http://asr.yzz.me/');
		$fileContent = str_ireplace('ё', 'е', $fileContent);
        $results = [];
        if (isset($fileContent)) {
			while (empty($results[0])) {//
				preg_match_all("#$text(.*?)вопрос#is", mb_strtolower($fileContent), $results);
				$text = mb_substr(trim($text), mb_strpos(trim($text), ' '));
				if(strlen($text) < 10 || !str_contains($text, ' ')){break;}
			}
		}
        // Выводим результаты
        if (!empty($results[0])) {
			$response = "Результаты поиска:\n\r";
            foreach ($results[0] as $result) {
				$result = str_ireplace('вопрос', '', $result);
				if(str_contains($result,"<img ")){
					preg_match("#src=\"(.*?)\" #is", $result, $url_imgs);
					$url_img=$url_imgs[1];
					$caption=strip_tags($result);
					$chat_id=$chatId;
					$bot_url    = "https://api.telegram.org/bot{$botToken}/";
					$url        = $bot_url . "sendPhoto?chat_id=" . $chat_id ;

					$post_fields = array(   'chat_id'   => $chat_id,
											'caption'   => $caption,
											'photo'     => new CURLFile(realpath($url_img))
					);

					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						"Content-Type:multipart/form-data"
					));
					curl_setopt($ch, CURLOPT_URL, $url); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
					$output = curl_exec($ch);
					$results = [];
					$response = "";
				}
				else{
					$response .= $result."\n\r";
				}
            }
			if(strlen($response)>3999){
				$response = mb_strimwidth($response, 0, 4000, "...");
				$response = "<b>Чем точнее сформулирован вопрос, тем больше вероятность получить желаемый ответ...\n\r Попробуйте ещё раз.</b>\n\r\n\r".$response;
			}
			
        } 
		else {
            $response = "По запросу: " . $searchQuery . " - ничего не найдено.\n\r Попробуйте изменить запрос или воспользуйтесь <a href=\"https://asr.yzz.me/parcer_search_test.php\">поиском на сайте</a>";
        }
	}
	
	if ($text == "поисковые запросы" || $text == "поисковые запросы к боту" || $text == "что ищут спасатели" || $text == "запросы спасателей" || $text == "запросы к боту") {
		$response = file_get_contents("bot.txt");
		$response = substr($response,-3700);
    }

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$chatId}&parse_mode=html&text=" . urlencode($response);
    file_get_contents($url);
}
?>
