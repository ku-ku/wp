<template>
    <v-text-field v-on:input="oninput" 
                  placeholder="поиск"
                  dense
                  clearable
                  style="max-width:15rem;margin-right:1rem;"
                  hide-details>
        <template v-slot:append>
            <v-icon>mdi-magnify</v-icon>
        </template>
    </v-text-field>
</template>
<script>
var hTimer = false;

export default {
    name: 'WpSearchField',
    data(){
        return {
            s: null
        };
    },
    methods: {
        oninput(s){
            if (!!hTimer){
                clearTimeout(hTimer);
            }
            hTimer = setTimeout(()=>{
                hTimer = false;
                this.s = s;
                this.$emit("filter", s);
            }, 500);
        },
        reset(){
            this.s = null;
            //$(this.$el).find("input").val("");
            $(this.$el).find("button").trigger("click");
        }
    },
    computed: {
        val(){
            return this.s;
        }
    }
}
</script>