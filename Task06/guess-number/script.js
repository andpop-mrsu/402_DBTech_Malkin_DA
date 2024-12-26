let db;

function initIndexedDB() {
    const request = indexedDB.open('GameDatabase', 1);

    request.onupgradeneeded = function(event) {
        db = event.target.result;
        const objectStore = db.createObjectStore('games', { keyPath: 'id', autoIncrement: true });
        objectStore.createIndex('playerName', 'playerName', { unique: false });
        objectStore.createIndex('result', 'result', { unique: false });
    };

    request.onsuccess = function(event) {
        db = event.target.result;
    };

    request.onerror = function(event) {
        console.error('Ошибка при открытии базы данных', event.target.error);
    };
}

function saveToIndexedDB(gameResult) {
    const transaction = db.transaction(['games'], 'readwrite');
    const objectStore = transaction.objectStore('games');
    const request = objectStore.add(gameResult);

    request.onsuccess = function() {
        console.log('Результат игры сохранен');
    };

    request.onerror = function(event) {
        console.error('Ошибка при сохранении результата игры', event.target.error);
    };
}

initIndexedDB();