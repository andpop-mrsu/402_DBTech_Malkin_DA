<?php

namespace Markause\GuessNumber\Controller;

use Markause\GuessNumber\View;
use Markause\GuessNumber\Game;

function startGame($maxNumber, $maxAttempts, $saveToDatabase = false) {
	if ($saveToDatabase) {
        \cli\line("Пока что базы данных не работают.");
    }
    $game = new Game($maxNumber, $maxAttempts);
    $game->play();
}
