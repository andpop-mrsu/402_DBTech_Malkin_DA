<?php

namespace Markause\GuessNumber;

use function cli\prompt;
use function cli\line;

class Game {
    private $maxNumber;
    private $maxAttempts;
    private $secretNumber;
    private $attempts;
    private $attemptsHistory = [];
    private $playerName;

    public function __construct($maxNumber, $maxAttempts, $playerName = 'Player') {
        $this->maxNumber = $maxNumber;
        $this->maxAttempts = $maxAttempts;
        $this->secretNumber = rand(1, $maxNumber);
        $this->attempts = 0;
        $this->playerName = $playerName;
    }

    public function guess($number) {
        $this->attempts++;
        $result = $number == $this->secretNumber ? 'win' : ($number < $this->secretNumber ? 'less' : 'greater');
        $this->attemptsHistory[] = [
            'number' => $this->attempts,
            'guess' => $number,
            'result' => $result
        ];
        return $result;
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

    public function getAttempts() {
        return $this->attemptsHistory;
    }

    public function getPlayerName() {
        return $this->playerName;
    }

    public function play() {
        line("Угадайте число от 1 до {$this->maxNumber}. У вас есть {$this->maxAttempts} попыток.");

        while (true) {
            $guess = (int)prompt("Введите вашу догадку: ");
            $result = $this->guess($guess);

            if ($result === 'win') {
                line("Поздравляем! Вы угадали число {$this->secretNumber}!");
                break;
            } elseif ($result === 'less') {
                line("Загаданное число больше, чем $guess.");
            } else {
                line("Загаданное число меньше, чем $guess.");
            }

            if ($this->isGameOver()) {
                line("К сожалению, вы исчерпали все попытки. Загаданное число было {$this->secretNumber}.");
                break;
            } else {
                line("Осталось попыток: {$this->getAttemptsLeft()}.");
            }
        }
    }
}