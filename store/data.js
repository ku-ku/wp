export const state = ()=>({
    user: null,
    divisions: null
});

export const mutations = {
    set(state, payload){
        if (!!payload){
            if (payload.hasOwnProperty("user")){
                state.user = payload.user;
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
                })
            }
        });
    },   //list

};      //actions