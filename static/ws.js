const KEYS = ["divisions", "staffing", "employees", "places"];

let dbPromise = null;
let env = null;  //env-context

onmessage = function(e){
    switch (e.data.type) {
        case "init":
            env = e.data.env;
            env.hTimer = setInterval(_ping, 5*60*1000);
            idbOpen();
            setTimeout(actualizze, 1000);
            break;
        case "read":
            readData();
            break;
        case "save":
            idbSave(e.data.data);
            break;
    }
};  //onmessage


const _ping = ()=>{
    fetch(`${env.apiUrl}?q=ping`)
            .then( r => {})
            .catch( e => {
                console.log('ERR (ping)', e);
                postMessage( {success:false, type:"lost"} );
            });
};

const idbOpen = ()=>{
    var db = null;
    dbPromise = new Promise( (resolve, reject) =>{
        if (typeof indexedDB === "undefined"){
            reject({error: "No indexedDB available"});
        } else if ( db ){
                resolve(db);
        } else {
            const req = indexedDB.open("work-plan", 2);
            req.onsuccess = e => {
                db = req.result;
                resolve(db);
            };
            req.onerror = e => {
                console.log('ERR (db)', e);
                reject({error:"ERR: can`t IDB open"});
            };
            req.onupgradeneeded = e => {
                db = e.target.result;
                db.createObjectStore("info");
            };    
        }
    });
};  //idbRead

const readData = async ()=>{
    if (!dbPromise) {
        console.log('No db available');
        return;
    }
    
    const db = await dbPromise,
        _get = name => {
            return new Promise((resolve, reject) => {
                const obs = db.transaction("info").objectStore("info");
                const res = obs.get(name);
                res.onsuccess = e => {
                    const o = {success: true, type: "read"};
                    eval('o[name]=' + e.target.result);
                    postMessage( o );
                    resolve(name);
                };
                res.onerror = e => {
                    console.log(`ERR on read "${name}"`, res, e);
                    reject(name);
                };
            });
         };
    KEYS.map( async k => { 
        try {
            await _get(k);
        } catch(e){}
    });
};  //readData


const idbSave = async e => {
    if (!dbPromise) {
        console.log('WK (saving): no database');
        return;
    }
    
    const db = await dbPromise;
    
    const _save = (key, data) =>{
        return new Promise((resolve, reject) => {
            
            const obs = db.transaction("info", "readwrite").objectStore("info"),
                _save2 = ()=>{
                    const res = obs.add(JSON.stringify(data), key);
                    res.onsuccess = e => {
                        resolve(e);
                    };
                    res.onerror = e => {
                        reject(e);
                    };
                };

            const req = obs.delete(key);
            req.onsuccess = _save2;
            req.onerror = _save2;
            
        });
    };  //_save
    
    Object.keys( e ).map( async k => {
        const n = KEYS.findIndex( k2 => k===k2 );
        if ( n > -1 ){
            try {
                await _save(k, e[k]);
            } catch(e){
                console.log('ERR (save)', e);
            }
        }
    });
};  //idbSave

const actualizze = () => {
    var errs = 0, n = 0,
    _load = ()=>{
        if ( n > KEYS.length -1 ){
            return;
        }
        
        const that = KEYS[ n ];
        
        fetch(`${ env.apiUrl }?q=${ that }`)
                .then( r => r.json() )
                .then( async res => {
                            const o = {};
                            o[that] = res;
                            await idbSave( o );
                            o.success = true;
                            o.type = "read";
                            postMessage( o );
                            n++;
                            setTimeout(_load, 500);
                })
                .catch( e => {
                    console.log('ERR (actz)', e);
                    errs++;
                    if (errs < 10){
                        setTimeout(_load, 1000);
                    }
                });
    };
    
    _load();
};  //reloadCache