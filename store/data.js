import { empty } from "~/utils";

export const state = ()=>({
    user: null,
    divisions: null,
    staffing: null
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
     * @param {Object} payload exampl: {users|divisions:{ID+, ...}}
     */
    upd(state, payload){
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
    async list({state, commit}, payload){
        return new Promise((resolve, reject)=>{
            if (!!state[payload]){
                resolve(state[payload]);
            } else {
                $nuxt.api(payload).then(res => {
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
     * Change data-action
     * @param {Object} payload item for updating
     * @returns {Promise}
     */
    async upd({commit}, payload){
        return new Promise((resolve, reject)=>{
            Object.keys(payload).map( k => {
                const item = payload[k];
                $nuxt.api(k, {
                            action: "save",
                            item
                }).then( data => {
                    if (!!data.success){
                        payload[k].ID = data.ID;
                        commit("upd", payload);
                        resolve();
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