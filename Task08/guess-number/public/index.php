<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

// Редирект на index.html
$app->get('/', function ($request, $response) {
    return $response
        ->withHeader('Location', '/index.html')
        ->withStatus(302);
});

// Получение всех игр
$app->get('/games', function ($request, $response) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $result = $db->query('SELECT * FROM games');
    $games = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $games[] = $row;
    }
    $response->getBody()->write(json_encode($games));
    return $response->withHeader('Content-Type', 'application/json');
});

// Получение игры с шагами
$app->get('/games/{id}', function ($request, $response, $args) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $id = (int)$args['id'];

    // Получение данных о самой игре
    $gameResult = $db->querySingle("SELECT * FROM games WHERE id = $id", true);
    
    // Получение шагов игры
    $stepsResult = $db->query("SELECT * FROM steps WHERE game_id = $id");
    $steps = [];
    while ($row = $stepsResult->fetchArray(SQLITE3_ASSOC)) {
        $steps[] = $row;
    }

    if ($gameResult) {
        // Добавляем шаги к информации о самой игре
        $gameResult['steps'] = $steps;
        $response->getBody()->write(json_encode($gameResult));
        return $response->withHeader('Content-Type', 'application/json');
    }

    return $response->withStatus(404)->write('Game not found');
});

// Добавление новой игры
$app->post('/games', function ($request, $response) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $data = json_decode($request->getBody()->getContents(), true);
    $stmt = $db->prepare('INSERT INTO games (playerName, maxNumber, attempts, result, timestamp) VALUES (?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $data['playerName']);
    $stmt->bindValue(2, $data['maxNumber']);
    $stmt->bindValue(3, $data['attempts']);
    $stmt->bindValue(4, $data['result']);
    $stmt->bindValue(5, $data['timestamp']);
    $stmt->execute();
    $id = $db->lastInsertRowID();
    $response->getBody()->write(json_encode(['id' => $id]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Добавление хода
$app->post('/step/{id}', function ($request, $response, $args) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $id = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);
    $stmt = $db->prepare('INSERT INTO steps (game_id, guess, result) VALUES (?, ?, ?)');
    $stmt->bindValue(1, $id);
    $stmt->bindValue(2, $data['guess']);
    $stmt->bindValue(3, $data['result']);
    $stmt->execute();
    return $response->withStatus(201);
});

// Обновление статуса игры
$app->patch('/games/{id}', function ($request, $response, $args) {
    $db = new SQLite3(__DIR__ . '/../db/games.db');
    $id = (int)$args['id'];
    $data = json_decode($request->getBody()->getContents(), true);
    $stmt = $db->prepare('UPDATE games SET result = ? WHERE id = ?');
    $stmt->bindValue(1, $data['result']);
    $stmt->bindValue(2, $id);
    $stmt->execute();
    return $response->withStatus(200);
});

$app->run();