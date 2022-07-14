<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="12">
            <v-text-field label="Наименование" 
                          v-model="item.UF_NAME"
                          clearable
                          :error="errs.UF_NAME"
                          hide-details
                          required>
            </v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="6">
            <v-text-field 
                v-model="item.UF_SORT"
                label="Порядок сортировки"
                :error="errs.UF_SORT"
                style="max-width: 10rem;"
                hide-details>
            </v-text-field>
        </v-col>
        <v-col cols="6">
            <v-checkbox
                v-model="item.UF_DISABLE"
                label="Не использовать"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import { DIA_MODES, empty } from "~/utils/";
import { mxForm } from '~/utils/mxForm.js';

export default {
    name: 'WpFrmStaff',
    mixins: [ mxForm ],
    data(){
        return {
            item: {},
            errs: {}
        };
    },
    methods: {
        validate(){
            if ( empty(this.item.UF_NAME) ){
                this.errs["UF_NAME"] = true;
                return false;
            }
            if ( !(Number(this.item.UF_SORT) > 0) ){
                this.errs["UF_SORT"] = true;
                return false;
            }
            return true;
        },
        async save(){
            try {
                await this.$store.dispatch("data/upd", {staffing: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save
    }
}
</script>