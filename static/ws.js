let db;

self.onmessage = (e) => {
    switch (e.type) {    
        case "init":
            idbOpen();
            break;
    }
};  //onmessage

const idbOpen = ()=>{
    const indexedDB = indexedDB || msIndexedDB || mozIndexedDB || webkitIndexedDB || OIndexedDB;
    if (typeof indexedDB == "undefined"){
        return false;
    }
    const req = indexedDB.open("work-plan", 1);
    req.onsuccess = function(e) {
          db = req.result;
    };
    req.onerror = function(e) {
        console.log('ERR (db)', e);
        self.postMessage("ERR: can`t IDB open");
    };
};  //idbRead