<?php

namespace Markause\GuessNumber;

class Database {
    private $db;

    public function __construct($dbPath) {
        // Проверка существования каталога и создание его, если необходимо
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Попытка открыть базу данных
        $this->db = new \SQLite3($dbPath);
        if (!$this->db) {
            throw new \Exception("Unable to open database: " . $this->db->lastErrorMsg());
        }

        $this->createTables();
    }

    private function createTables() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            player_name TEXT,
            max_number INTEGER,
            secret_number INTEGER,
            result TEXT,
            date TEXT
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game_id INTEGER,
            attempt_number INTEGER,
            guessed_number INTEGER,
            result TEXT,
            FOREIGN KEY (game_id) REFERENCES games(id)
        )");
    }

    public function saveGame($game) {
        $stmt = $this->db->prepare("INSERT INTO games (player_name, max_number, secret_number, result, date) 
                                    VALUES (:player_name, :max_number, :secret_number, :result, :date)");
        $stmt->bindValue(':player_name', $game->getPlayerName(), SQLITE3_TEXT);
        $stmt->bindValue(':max_number', $game->getMaxNumber(), SQLITE3_INTEGER);
        $stmt->bindValue(':secret_number', $game->getSecretNumber(), SQLITE3_INTEGER);
        $stmt->bindValue(':result', $game->isGameOver() ? 'lose' : 'win', SQLITE3_TEXT);
        $stmt->bindValue(':date', date('Y-m-d H:i:s'), SQLITE3_TEXT);
        $stmt->execute();

        $gameId = $this->db->lastInsertRowID();

        foreach ($game->getAttempts() as $attempt) {
            $stmt = $this->db->prepare("INSERT INTO attempts (game_id, attempt_number, guessed_number, result) 
                                        VALUES (:game_id, :attempt_number, :guessed_number, :result)");
            $stmt->bindValue(':game_id', $gameId, SQLITE3_INTEGER);
            $stmt->bindValue(':attempt_number', $attempt['number'], SQLITE3_INTEGER);
            $stmt->bindValue(':guessed_number', $attempt['guess'], SQLITE3_INTEGER);
            $stmt->bindValue(':result', $attempt['result'], SQLITE3_TEXT);
            $stmt->execute();
        }

        // Отладочный вывод
        echo "Game saved with ID: $gameId\n";
    }

    public function getGames() {
        $result = $this->db->query("SELECT * FROM games ORDER BY date DESC");
        $games = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $games[] = $row;
        }
        return $games;
    }

    public function getGamesByResult($result) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE result = :result ORDER BY date DESC");
        $stmt->bindValue(':result', $result, SQLITE3_TEXT);
        $result = $stmt->execute();
        $games = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $games[] = $row;
        }
        return $games;
    }

    public function getGame($id) {
        $stmt = $this->db->prepare("SELECT * FROM games WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }

    public function getAttempts($gameId) {
        $stmt = $this->db->prepare("SELECT * FROM attempts WHERE game_id = :game_id ORDER BY attempt_number ASC");
        $stmt->bindValue(':game_id', $gameId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $attempts = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $attempts[] = $row;
        }
        return $attempts;
    }

    public function getPlayerStats() {
        $result = $this->db->query("SELECT player_name, 
                                           COUNT(CASE WHEN result = 'win' THEN 1 END) AS wins,
                                           COUNT(CASE WHEN result = 'lose' THEN 1 END) AS losses
                                    FROM games
                                    GROUP BY player_name
                                    ORDER BY wins DESC");
        $stats = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $stats[] = $row;
        }
        return $stats;
    }
}