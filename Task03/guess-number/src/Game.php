<?php

namespace Markause\GuessNumber;

use Markause\GuessNumber\View;
use function Markause\GuessNumber\View\render;
use function cli\prompt;
use function cli\line;

class Game {
    private $maxNumber;
    private $maxAttempts;
    private $secretNumber;
    private $attempts;

    public function __construct($maxNumber, $maxAttempts) {
        $this->maxNumber = $maxNumber;
        $this->maxAttempts = $maxAttempts;
        $this->secretNumber = rand(1, $maxNumber);
        $this->attempts = 0;
    }

    public function guess($number) {
        $this->attempts++;

        if ($number == $this->secretNumber) {
            return 'win';
        } elseif ($number < $this->secretNumber) {
            return 'less';
        } else {
            return 'greater';
        }
    }

    public function isGameOver() {
        return $this->attempts >= $this->maxAttempts;
    }

    public function getAttemptsLeft() {
        return $this->maxAttempts - $this->attempts;
    }

    public function getSecretNumber() {
        return $this->secretNumber;
    }

    public function getMaxNumber() {
        return $this->maxNumber;
    }

    public function getMaxAttempts() {
        return $this->maxAttempts;
    }
	
	function play() {
		if (php_sapi_name() === 'cli') {
			// Ввод параметров через командную строку
			$input = prompt("Введите максимальное число и количество попыток (maxNumber, maxAttempts):");
			$input = trim($input);
			if (strpos($input, ',') === false) {
				line("Неверный формат ввода.");
				return;
			}
			list($maxNumber, $maxAttempts) = explode(',', $input);
			$maxNumber = (int)trim($maxNumber);
			$maxAttempts = (int)trim($maxAttempts);
		} else {
			// Получаем параметры из GET-запроса или используем значения по умолчанию
			$maxNumber = isset($_GET['maxNumber']) ? (int)$_GET['maxNumber'] : 100;
			$maxAttempts = isset($_GET['maxAttempts']) ? (int)$_GET['maxAttempts'] : 10;
		}

		// Создаем экземпляр игры с заданными параметрами
		$game = new Game($maxNumber, $maxAttempts);

		// Отображаем игру
		render($game);
	}
}