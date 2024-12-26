document.addEventListener('DOMContentLoaded', function () {
    let maxNumber, attempts, gameOver = false, playerName;
    let gameId = null;
    let moves = []; // Хранение шагов игрока
    let secretNumber; // Добавляем переменную для секретного числа
    let remainingAttempts; // Добавляем переменную для количества оставшихся попыток

    const startBtn = document.getElementById('startBtn');
    const viewPastGamesBtn = document.getElementById('view-past-games');
    const viewWonGamesBtn = document.getElementById('view-won-games');
    const viewLostGamesBtn = document.getElementById('view-lost-games');
    const viewStatsBtn = document.getElementById('view-stats');
    const helpBtn = document.getElementById('help');
    const closeHelpBtn = document.getElementById('close-help');
    const replayContainer = document.getElementById('replay-container');
    const closeReplayBtn = document.getElementById('close-replay');

    startBtn.addEventListener('click', startNewGame);
    viewPastGamesBtn.addEventListener('click', displayPastGames);
    viewWonGamesBtn.addEventListener('click', displayWonGames);
    viewLostGamesBtn.addEventListener('click', displayLostGames);
    viewStatsBtn.addEventListener('click', displayPlayerStats);
    helpBtn.addEventListener('click', () => {
        document.getElementById('help-container').style.display = 'block';
    });
    closeHelpBtn.addEventListener('click', () => {
        document.getElementById('help-container').style.display = 'none';
    });
    closeReplayBtn.addEventListener('click', () => {
        replayContainer.style.display = 'none';
    });

    async function startNewGame() {
        playerName = document.getElementById('player-name').value.trim();
        if (!playerName) {
            alert('Пожалуйста, введите ваше имя перед началом игры.');
            return;
        }

        maxNumber = parseInt(document.getElementById('max-number').value);
        attempts = parseInt(document.getElementById('attempts').value);

        gameOver = false;
        moves = []; // Сброс шагов при новой игре
        remainingAttempts = attempts; // Инициализация количества оставшихся попыток

        document.getElementById('max-number-display').innerText = maxNumber;
        document.querySelector('.game-container').style.display = 'block';
        document.getElementById('result').innerText = `Осталось попыток: ${remainingAttempts}`;

        // Отправляем данные о новой игре на сервер
        const response = await fetch('/games', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                playerName,
                maxNumber,
                attempts,
                result: 'In Progress',
                timestamp: new Date().toISOString()
            })
        });
        const result = await response.json();
        gameId = result.id;

        // Генерируем случайное число
        secretNumber = Math.floor(Math.random() * maxNumber) + 1;
    }

    async function saveGameResult(result) {
        if (gameId) {
            moves.forEach(async (move) => {
                await fetch(`/step/${gameId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        guess: move.guess,
                        result: move.result
                    })
                });
            });

            // Сохраняем статус игры
            await fetch(`/games/${gameId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    result
                })
            });
        }
    }

    async function displayPastGames() {
        const response = await fetch('/games');
        const games = await response.json();

        const gameList = document.getElementById('game-list');
        gameList.innerHTML = '';
        games.forEach(game => {
            const listItem = document.createElement('li');
            const timestamp = new Date(game.timestamp).toLocaleString('ru-RU', { timeZone: 'Europe/Moscow' });
            listItem.textContent = `Игрок: ${game.playerName} - Игра на ${timestamp} - Максимальное число: ${game.maxNumber} - Попытки: ${game.attempts} - Результат: ${game.result}`;
            listItem.addEventListener('click', () => replayGame(game.id));
            gameList.appendChild(listItem);
        });
    }

    async function displayWonGames() {
        const response = await fetch('/games');
        const games = await response.json();

        const gameList = document.getElementById('game-list');
        gameList.innerHTML = '';
        games.filter(game => game.result === 'Won').forEach(game => {
            const listItem = document.createElement('li');
            const timestamp = new Date(game.timestamp).toLocaleString('ru-RU', { timeZone: 'Europe/Moscow' });
            listItem.textContent = `Игрок: ${game.playerName} - Игра на ${timestamp} - Максимальное число: ${game.maxNumber} - Попытки: ${game.attempts} - Результат: ${game.result}`;
            listItem.addEventListener('click', () => replayGame(game.id));
            gameList.appendChild(listItem);
        });
    }

    async function displayLostGames() {
        const response = await fetch('/games');
        const games = await response.json();

        const gameList = document.getElementById('game-list');
        gameList.innerHTML = '';
        games.filter(game => game.result === 'Lost').forEach(game => {
            const listItem = document.createElement('li');
            const timestamp = new Date(game.timestamp).toLocaleString('ru-RU', { timeZone: 'Europe/Moscow' });
            listItem.textContent = `Игрок: ${game.playerName} - Игра на ${timestamp} - Максимальное число: ${game.maxNumber} - Попытки: ${game.attempts} - Результат: ${game.result}`;
            listItem.addEventListener('click', () => replayGame(game.id));
            gameList.appendChild(listItem);
        });
    }

    async function displayPlayerStats() {
        const response = await fetch('/games');
        const games = await response.json();

        const playerStats = {};

        games.forEach(game => {
            if (!playerStats[game.playerName]) {
                playerStats[game.playerName] = { wins: 0, losses: 0 };
            }
            if (game.result === 'Won') {
                playerStats[game.playerName].wins++;
            } else if (game.result === 'Lost') {
                playerStats[game.playerName].losses++;
            }
        });

        const sortedPlayers = Object.keys(playerStats).sort((a, b) => {
            return playerStats[b].wins - playerStats[a].wins;
        });

        const statsList = document.getElementById('game-list');
        statsList.innerHTML = '';
        sortedPlayers.forEach(player => {
            const listItem = document.createElement('li');
            listItem.textContent = `Игрок: ${player} - Побед: ${playerStats[player].wins} - Поражений: ${playerStats[player].losses}`;
            statsList.appendChild(listItem);
        });
    }

    async function replayGame(id) {
        const response = await fetch(`/games/${id}`);
        const gameData = await response.json();

        replayContainer.style.display = 'block';
        const replayList = document.getElementById('replay-list');
        replayList.innerHTML = `<strong>Повтор игры:</strong> Игрок: ${gameData.playerName} | Максимальное число: ${gameData.maxNumber} | Попытки: ${gameData.attempts} | Результат: ${gameData.result}`;

        if (gameData.steps && gameData.steps.length > 0) {
            const stepsList = document.createElement('ul');
            gameData.steps.forEach((step, index) => {
                const stepItem = document.createElement('li');
                stepItem.textContent = `Ход ${index + 1}: Число: ${step.guess} - Результат: ${step.result}`;
                stepsList.appendChild(stepItem);
            });
            replayList.appendChild(stepsList);
        } else {
            const noMoves = document.createElement('p');
            noMoves.textContent = 'Нет записей о ходах для этой игры.';
            replayList.appendChild(noMoves);
        }
    }

    document.getElementById('submit-guess').addEventListener('click', async () => {
        if (gameOver) return;

        const guess = parseInt(document.getElementById('guess').value);
        if (isNaN(guess) || guess < 1 || guess > maxNumber) {
            alert('Пожалуйста, введите корректное число.');
            return;
        }

        moves.push({ guess, result: '' });
        remainingAttempts--; // Уменьшаем количество оставшихся попыток

        if (guess === secretNumber) {
            moves[moves.length - 1].result = 'Угадал!';
            gameOver = true;
            document.getElementById('result').innerText = 'Вы угадали число!';
            await saveGameResult('Won');
        } else {
            // Исправленная логика вывода подсказок
            const hint = guess < secretNumber ? 'Введенное число меньше загаданного' : 'Введенное число больше загаданного';
            moves[moves.length - 1].result = hint;
            document.getElementById('result').innerText = `${hint}. Осталось попыток: ${remainingAttempts}`;

            if (remainingAttempts === 0) {
                gameOver = true;
                document.getElementById('result').innerText = `Вы проиграли! Загаданное число было: ${secretNumber}`;
                await saveGameResult('Lost');
            }
        }
    });
});