export const mxForm = {
    fetchOnServer: false,
    computed: {
        loading(){
            return this.$fetchState.pending;
        }
    },
    methods: {
        has(q){
            switch(q){
                case "add":
                    return !((this.item?.ID || -1) > 0);
            }
        },
        use(item){
            this.item = {...item};
        },
        validate(){
            return false;
        },
        async save(){
            console.log('saving...', this.item);
        }
    }
};