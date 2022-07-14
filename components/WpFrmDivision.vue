<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="12">
            <v-text-field label="Наименование" 
                          v-model="item.UF_NAME"
                          :error="errs.UF_NAME"
                          required>
            </v-text-field>
        </v-col>
    </v-row>
    <v-row justify="space-between">
        <v-col cols="auto">
            <v-checkbox
                v-model="item.UF_ACTIVE"
                label="Активно"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="3">
            <v-text-field label="Код" 
                          v-model="item.UF_CODE"
                          :error="errs.UF_CODE"
                          required>
            </v-text-field>
        </v-col>
        <v-col cols="3">
            <v-text-field label="Порядок для сортировки" 
                          v-model="item.UF_SORT"
                          :error="errs.UF_SORT"
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
    name: 'WpFrmDivision',
    mixins: [ mxForm ],
    data(){
        return {
            item: {},
            errs: {}
        };
    },
    methods: {
        validate(){
            const _RQS = ["UF_CODE", "UF_NAME", "UF_SORT"],
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
                await this.$store.dispatch("data/upd", {divisions: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save

    }
}
</script>