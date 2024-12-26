<?php

namespace Markause\GuessNumber\View;

function render($game) {
    echo "Угадайте число от 1 до {$game->getMaxNumber()}. У вас есть {$game->getMaxAttempts()} попыток. ";

    if (php_sapi_name() === 'cli') {
        // Обработка ввода из командной строки
        while (true) {
            echo "Введите вашу догадку: ";
            $guess = (int)trim(fgets(STDIN));
            $result = $game->guess($guess);

            if ($result === 'win') {
                echo "Поздравляем! Вы угадали число {$game->getSecretNumber()}!\n";
                break;
            } elseif ($result === 'less') {
                echo "Загаданное число больше, чем $guess.\n";
            } else {
                echo "Загаданное число меньше, чем $guess.\n";
            }

            if ($game->isGameOver()) {
                echo "К сожалению, вы исчерпали все попытки. Загаданное число было {$game->getSecretNumber()}.\n";
                break;
            } else {
                echo "Осталось попыток: {$game->getAttemptsLeft()}.\n";
            }
        }
    }
}
