<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="12">
            <v-text-field label="Наименование" 
                          v-model="item.UF_NAME"
                          clearable
                          :error="errs.name"
                          required></v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
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
            errs: {
                name: false
            }
        };
    },
    methods: {
        validate(){
            if ( empty(this.item.UF_NAME) ){
                this.errs["name"] = true;
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