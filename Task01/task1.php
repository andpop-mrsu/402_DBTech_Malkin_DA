<?php


$readmeFile = 'README.md';


if (!file_exists($readmeFile)) {
echo "Файл README.md не найден: $readmeFile\n";
exit(1);
}


$readmeContent = file_get_contents($readmeFile);


$pattern = '/Проект представляет(.*?)(?=## |$)/s';


if (preg_match($pattern, $readmeContent, $matches)) {
$taskDescription = trim($matches[1]);


echo "Инструкция к игре \"Угадай число\" (Guess-number)\n";
echo "-------------------------------------------\n";
echo "Проект представляет ",$taskDescription;
} else {
echo "Описание задачи не найдено в файле README.md\n";
exit(1);
}

exit(0);
