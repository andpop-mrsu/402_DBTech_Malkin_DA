<?php

namespace Markause\GuessNumber;

use RedBeanPHP\R as R;

class Database
{
    public function __construct($dbPath)
    {
        // Проверка существования каталога и создание его, если необходимо
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Подключение к базе данных SQLite, если она еще не настроена
        if (!R::testConnection()) {
            R::setup("sqlite:$dbPath");
        }

        // Проверка на подключение
        if (!R::testConnection()) {
            throw new \Exception("Unable to connect to the database.");
        }

        $this->createTables();
    }

    private function createTables()
    {
        // Создание таблицы games, если она не существует
        if (!R::findOne('games')) {
            R::exec("CREATE TABLE games (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                player_name TEXT,
                max_number INTEGER,
                secret_number INTEGER,
                result TEXT,
                date TEXT
            )");
        }

        // Создание таблицы attempts, если она не существует
        if (!R::findOne('attempts')) {
            R::exec("CREATE TABLE attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                game_id INTEGER,
                attempt_number INTEGER,
                guessed_number INTEGER,
                result TEXT,
                FOREIGN KEY (game_id) REFERENCES games(id)
            )");
        }
    }

    public function saveGame($game)
    {
        // Сохранение игры
        $gameBean = R::dispense('games');
        $gameBean->player_name = $game->getPlayerName();
        $gameBean->max_number = $game->getMaxNumber();
        $gameBean->secret_number = $game->getSecretNumber();
        $gameBean->result = $game->isGameOver() ? 'lose' : 'win';
        $gameBean->date = date('Y-m-d H:i:s');
        $gameId = R::store($gameBean);

        // Сохранение попыток
        foreach ($game->getAttempts() as $attempt) {
            $attemptBean = R::dispense('attempts');
            $attemptBean->game_id = $gameId;
            $attemptBean->attempt_number = $attempt['number'];
            $attemptBean->guessed_number = $attempt['guess'];
            $attemptBean->result = $attempt['result'];
            R::store($attemptBean);
        }

        // Отладочный вывод
        echo "Game saved with ID: $gameId\n";
    }

    public function getGames()
    {
        return R::findAll('games', 'ORDER BY date DESC');
    }

    public function getGamesByResult($result)
    {
        return R::find('games', 'result = ? ORDER BY date DESC', [$result]);
    }

    public function getGame($id)
    {
        return R::load('games', $id);
    }

    public function getAttempts($gameId)
    {
        return R::find('attempts', 'game_id = ? ORDER BY attempt_number ASC', [$gameId]);
    }

    public function getPlayerStats()
    {
        return R::getAll("SELECT player_name, 
                                  COUNT(CASE WHEN result = 'win' THEN 1 END) AS wins,
                                  COUNT(CASE WHEN result = 'lose' THEN 1 END) AS losses
                           FROM games
                           GROUP BY player_name
                           ORDER BY wins DESC");
    }
}
