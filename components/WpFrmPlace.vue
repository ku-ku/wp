<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="12">
            <v-text-field label="Наименование" 
                          v-model="item.UF_PLACE"
                          :error="errs.UF_PLACE"
                          required>
            </v-text-field>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';
import { empty } from "~/utils/";

export default {
    name: 'WpFrmPlace',
    mixins: [ mxForm ],
    data(){
        return {
            item: {},
            errs: {}
        };
    },
    methods: {
        validate(){
            const _RQS = ["UF_PLACE"],
                  errs = { n: 0 };
            _RQS.map( r => {
                if ( empty(this.item[r]) ){
                    errs.n++;
                    errs[r] = true;
                }
            });
            this.errs = errs;
            return (errs.n === 0);
        },
        async save(){
            try {
                await this.$store.dispatch("data/upd", {places: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save
    }
}
</script>
