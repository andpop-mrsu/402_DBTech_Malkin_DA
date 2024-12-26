import { openDB } from './libs/idb.js';

// Открытие базы данных
export async function openDatabase() {
    return await openDB('GameDatabase', 1, {
        upgrade(db) {
            if (!db.objectStoreNames.contains('games')) {
                const store = db.createObjectStore('games', { keyPath: 'id', autoIncrement: true });
                store.createIndex('playerName', 'playerName', { unique: false });
                store.createIndex('result', 'result', { unique: false });
            }
        }
    });
}

// Сохранение результата игры
export async function saveGameResult(gameResult) {
    const db = await openDatabase();
    return await db.add('games', gameResult);
}

// Получение всех сохранённых игр
export async function getAllGames() {
    const db = await openDatabase();
    return await db.getAll('games');
}

// Получение всех побед
export async function getAllWins() {
    const db = await openDatabase();
    return await db.getAllFromIndex('games', 'result', 'win');
}

// Получение всех поражений
export async function getAllLosses() {
    const db = await openDatabase();
    return await db.getAllFromIndex('games', 'result', 'lose');
}

// Получение статистики
export async function getStatistics() {
    const [allGames, allWins, allLosses] = await Promise.all([
        getAllGames(),
        getAllWins(),
        getAllLosses()
    ]);

    return {
        totalGames: allGames.length,
        totalWins: allWins.length,
        totalLosses: allLosses.length
    };
}

// Получение игры по идентификатору
export async function getGameById(id) {
    const db = await openDatabase();
    return await db.get('games', id);
}