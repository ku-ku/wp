import { empty } from "~/utils";

const A_STATUSES = [
    {ID: 1, name: 'ПРОВЕДЕНО'},
    {ID: 2, name: 'Перенесено'},
    {ID: 9, name: 'Отменено'}
];

export const state = ()=>({
    statuses: A_STATUSES,
    user: null,
    divisions: null,
    staffing: null,
    employees: null,
    acts: null,
    reds: null
});

export const mutations = {
    /**
     * Set object collection to store
     * @param {Array} payload array items to store
     */
    set(state, payload){
        Object.keys(payload).map( k=>{
            state[k] = payload[k];
        });
    },
    /**
     * Update | set one object to store
     * @param {Object} payload exampl: { users|divisions|...: {ID+, ...} }
     */
    upd(state, payload){
        console.log('committing (upd)', payload);
        Object.keys(payload).map( k=>{
            const o = payload[k];
            if ( !Array.isArray(state[k]) ){
                state[k] = [];
            }
            const items = state[k];
            const n = items.findIndex( i=>i.ID === o.ID );
            if ( n < 0 ){
                items.push(o);
            } else {
                items.splice(n, 1, o);
            }
        });
    },  //upd
    /**
     * Remove object from collection
     * @param {Object} payload 
     */
    rm(state, payload){
        Object.keys(payload).map( k=>{
            if ( !Array.isArray(state[k]) ){
                return;
            }
            const n = state[k].findIndex( i => i.ID === payload[k].ID );
            if ( n > -1 ){
                state[k].splice(n, 1);
            }
        });
    }   //rm
};  //mutations
    

export const actions = {
    /**
     * Current user info
     * @returns {Promise}
     */
    async user({state, commit}){
        return new Promise((resolve, reject)=>{
            if (!!state.user){
                resolve(state.user);
            } else {
                $nuxt.api("user").then(user => {
                    commit("set", { user });
                    resolve(user);
                }).catch(e => {
                    console.log('ERR (user)', e);
                    reject(e);
                })
            }
        });
    },   //user
    async list({state, commit, rootGetters}, payload){
        return new Promise((resolve, reject)=>{
            const p = rootGetters["period"];
            if (!!state[payload]){
                resolve(state[payload]);
            } else {
                $nuxt.api(payload, {
                    period: {
                        start: p.start.toISOString(),
                        end: p.start.toISOString()
                    }
                }).then(res => {
                    const o = {};
                    o[payload] = res;
                    commit("set", o);
                    resolve(res);
                }).catch(e => {
                    console.log('ERR (data)', e);
                    reject(e);
                });
            }
        });
    },   //list
    /**
     * Read one entity by ID
     * @param {Object} payload {employees: <ID>}
     * @returns {Promise}
     */
    async one({commit}, payload){
        var q = "none", params = {ID: -1};
        Object.keys(payload).map( k => {
            q = k;
            params.ID=payload[k];
        });
        
        return new Promise((resolve, reject)=>{
            $nuxt.api(q, params).then(res => {
                        const o = {};
                        o[q] = res;
                        commit("upd", o);
                        resolve(res);
                    }).catch(e => {
                        console.log('ERR (data)', e);
                        reject(e);
                    });
        });
    },  //one
    /**
     * Change data-action
     * @param {Object} payload item for updating
     * @returns {Promise}
     */
    async upd({commit}, payload){
        return new Promise((resolve, reject)=>{
            Object.keys(payload).map( k => {
                const item = payload[k];
                
                //check date`s values
                Object.keys(item).map( f => {
                    if (item[f] instanceof Date){
                        item[f] = item[f].toISOString();
                    }
                } );
                
                $nuxt.api(k, {
                            action: "save",
                            item
                }).then( data => {
                    if (!!data.success){
                        const o = {};
                        o[k] = (data.item?.length > 0) 
                                    ? data.item[0]
                                    : Object.assign({ID: data.ID}, item);
                        commit("upd", o);
                        resolve(o);
                    } else {
                        throw {message: data.error};
                    }
                }).catch(e => {
                    reject(e);
                });
            });
        });
    },  //upd
    /**
     * Deleting item for collection
     * @param {Object} payload - item 
     * @returns {Promise}
     */
    async rm({commit}, payload){
        return new Promise((resolve, reject)=>{
            Object.keys(payload).map( k => {
                $nuxt.api(k, {
                            action: "del",
                            ID: payload[k].ID
                }).then( data => {
                    console.log("rm", data);
                    if (!!data.success){
                        commit("rm", payload);
                        resolve();
                    } else {
                        throw {message: data.error};
                    }
                }).catch(e => {
                    reject(e);
                });
            });
        });
    }   //rm

};      //actions

export const getters = {
    all: state => {
        var a = state.acts || [];
        return a.concat(state.reds);
    }
};