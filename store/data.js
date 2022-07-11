import { empty } from "~/utils";

export const state = ()=>({
    user: null,
    divisions: null,
    staffing: null
});

export const mutations = {
    set(state, payload){
        if (!!payload){
            if (payload.hasOwnProperty("divisions")){
                state.divisions = payload.divisions;
            }
            if (payload.hasOwnProperty("user")){
                state.user = payload.user;
            }
            if (payload.hasOwnProperty("staffing")){
                state.staffing = payload.staffing;
            }
        }
    },
    rm(state, payload){
        if ( payload.hasOwnProperty("staffing") ){
            const n = state.staffing?.findIndex( s=> s.ID === payload.staffing.ID );
            if ( n > -1 ){
                state.staffing.splice(n, 1);
            }
        }
    }
};  //mutations
    

export const actions = {
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
     * Deleting item for collection
     * @param {Object} payload - item 
     * @returns {Promise}
     */
    async rm({commit}, payload){
        console.log("rm", payload);
        return new Promise((resolve, reject)=>{
            var that, id;
            
            if ( payload.hasOwnProperty("staffing") ){
                that = "staffing";
                id = payload.staffing.ID;
            }
            
            if ( empty(that) ){
                reject({message: "Unknown operation"});
            } else {
                $nuxt.api(that, {
                            action: "del",
                            ID: id
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
            }
        });
    }   //rm

};      //actions