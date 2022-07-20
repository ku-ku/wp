<template>
<v-form class="wp-action">
    <v-row>
        <v-col cols="4">
            <wp-date-input label="Дата"
                           type="date"
                           :value="item.UF_ADT"
                           :error="errs.UF_ADT"
                           v-on:change="set('UF_ADT', $event)">
            </wp-date-input>
        </v-col>
        <v-col cols="8">
            <v-checkbox
                v-model="item.UF_YEARATTR"
                label="Ежегодный"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-textarea label="Наименование" 
                        rows="2" 
                        :error="errs.UF_TEXT"
                        v-model="item.UF_TEXT"></v-textarea>
        </v-col>
    </v-row>
    <v-row class="wp-action__meta" v-if="!has('add')">
        <v-col cols="12">
            <span><v-icon small>mdi-account</v-icon>{{item.UF_AUTHOR}}</span>
            <span><v-icon small>mdi-clock</v-icon>{{_fmt_dt(item.UF_INSTIME)}}</span>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import $moment from "moment";
import { mxForm } from '~/utils/mxForm.js';
import { empty } from '~/utils/';

export default {
    name: 'WpFrmRed',
    mixins: [ mxForm ],
    data(){
        return {
            item: {
                ID: -1
            },
            errs: {}
        };
    },
    methods: {
        set(q, val){
            switch(q){
                default:
                    this.item[q] = val;
                    break;
            }
        },   //set
        validate(){
            const _RQS = ["UF_ADT", "UF_TEXT"],
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
                this.item.UF_ADT = $moment(this.item.UF_ADT).toDate();
                await this.$store.dispatch("data/upd", {reds: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save
    }
}
</script>