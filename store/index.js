import $moment from "moment";

const _def_per = ()=>{
    const _d = new Date();
    const res = {
        start: $moment([_d.getFullYear(), _d.getMonth(), 1])
    };
    res.end = res.start.clone().add(1, 'months');
    return res;
};

export const state = ()=>({
    period: _def_per()
});

export const mutations = {
    default(state){
        state.period = _def_per();
    },
    /**
     * Set object collection to store
     * @param {Array} payload array items to store
     */
     set(state, payload){
        if (payload.hasOwnProperty("period")){
            const { period } = payload;
            state.period = {
                start: $moment(period.start),
                end: $moment(period.end)
            };
        } else {
            Object.keys(payload).map( k=>{
                state[k] = payload[k];
            });
        }
    }
};

export const getters = {
    user: async state => {
        return await $nuxt.$store.dispatch("data/user");
    },
    /**
     * 
     * @param {Object} state 
     * @returns {Object} period{start, end} as moment date's
     */
    period: state => state.period,
};