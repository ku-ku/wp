<template>
<v-snackbar v-model="hasSnackbar"
            bottom
            dark
            multiLine
            :color="snackbar.color"
            :timeout="snackbar.timeout">
        <div v-html="snackbar.text"></div>
        <template v-slot:action="{ attrs }">
            <v-btn icon
                   v-on:click="hasSnackbar=false">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </template>            
</v-snackbar>
</template>
<script>
import { 
        VSnackbar,
        VBtn,
        VIcon
} from 'vuetify/lib';
import { empty as isEmpty } from '~/utils';
    
export default {
    name: 'WpMsg',
    components: {
        VSnackbar,
        VBtn,
        VIcon
    },
    data(){
        return {
            snackbar: false //bool | {color,timeout,text}
        };
    },
    computed: {
        hasSnackbar: {
            get(){ return !!this.snackbar; },
            set(val){
                if (!val){
                    this.snackbar = false;
                }
            }
        }
    },
    methods: {
        show(e){
            if (
                    !(!!e)
                  ||isEmpty(e.text)
                ){
                this.snackbar = false;
                return;
            }
            const sb = {
                    color: (!!e.color) ?  e.color : "primary",
                    timeout: (!!e.timeout) ? e.timeout : 8000,
                    text: e.text
            };
            this.snackbar = sb;
        }
    }
};
</script>    
<style lang="scss">
    .v-snack {
        &__wrapper{
            font-size: 0.85rem;
            line-height: 1.125;
            & a:link, & a:visited{
                color: #fff !important;
                text-decoration: none !important;
                & > * {
                    color: #fff !important;
                }
            }
            & h1, & h2, & h3{
                margin: 0.25rem 0;
            }
        }
    }
</style>

