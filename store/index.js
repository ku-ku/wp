import $moment from "moment";
$moment.locale("ru");

const _def_per = ()=>{
    const _d = new Date();
    const res = {
        start: $moment([_d.getFullYear(), _d.getMonth(), 1])
    };
    res.end = res.start.clone().add(1, 'months');
    res.work = $moment();
    res.type = 'month';
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
            
            if (typeof period.start !== "undefined"){
                state.period.start = $moment(period.start);
            }
            if (typeof period.end !== "undefined"){
                state.period.end   = $moment(period.end).add(1, "days").add(-1, 'seconds');
            }
            if (typeof period.work !== "undefined"){
                const d = $moment(period.work);
                state.period.work = $moment(new Date(d.year(), d.month(), d.date()));
            }
            if (typeof period.type !== "undefined"){
                state.period.type = period.type;
            }
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
    period: state => state.period
};