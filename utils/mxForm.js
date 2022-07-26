import $moment from "moment";

export const mxForm = {
    fetchOnServer: false,
    computed: {
        loading(){
            return this.$fetchState.pending;
        }
    },
    methods: {
        _fmt_dt(d, f){
            var f = (typeof f === "undefined") ? "DD.MM.YYYY HH:mm:ss" : f;
            return (typeof d === "undefined") ? null : $moment(d).format(f);
        },
        has(q){
            switch(q){
                case "add":
                    return !((this.item?.ID || -1) > 0);
                default:
                    return (
                                this.item?.hasOwnProperty(q)
                             && !!this.item[q]
                           );
            }
        },
        set(q, v){
            this[q] = v;
        },
        use(item){
            this.item = { ...item };
            this.$nextTick(()=>{
                $($(this.$el).find("input").get(0)).trigger("focus");
            });
        },
        validate(){
            return false;
        },
        async save(){
            console.log('saving...', this.item);
        }
    }
};