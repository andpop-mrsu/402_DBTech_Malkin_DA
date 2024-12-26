let dbPromise;

function initIndexedDB() {
    dbPromise = idb.open('GameDatabase', 1, function(upgradeDb) {
        if (!upgradeDb.objectStoreNames.contains('games')) {
            const objectStore = upgradeDb.createObjectStore('games', { keyPath: 'id', autoIncrement: true });
            objectStore.createIndex('playerName', 'playerName', { unique: false });
            objectStore.createIndex('result', 'result', { unique: false });
        }
    });
}

function saveToIndexedDB(gameResult) {
    dbPromise.then(function(db) {
        const tx = db.transaction('games', 'readwrite');
        const store = tx.objectStore('games');
        store.add(gameResult);
        return tx.complete;
    }).then(function() {
        console.log('Результат игры сохранен');
    }).catch(function(error) {
        console.error('Ошибка при сохранении результата игры', error);
    });
}

function getAllGames() {
    return dbPromise.then(function(db) {
        const tx = db.transaction('games', 'readonly');
        const store = tx.objectStore('games');
        return store.getAll();
    });
}

function getAllWins() {
    return dbPromise.then(function(db) {
        const tx = db.transaction('games', 'readonly');
        const store = tx.objectStore('games');
        const index = store.index('result');
        return index.getAll('win');
    });
}

function getAllLosses() {
    return dbPromise.then(function(db) {
        const tx = db.transaction('games', 'readonly');
        const store = tx.objectStore('games');
        const index = store.index('result');
        return index.getAll('lose');
    });
}

function getStatistics() {
    return Promise.all([getAllGames(), getAllWins(), getAllLosses()]).then(function(results) {
        const allGames = results[0];
        const allWins = results[1];
        const allLosses = results[2];
        return {
            totalGames: allGames.length,
            totalWins: allWins.length,
            totalLosses: allLosses.length
        };
    });
}

initIndexedDB();