<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="12">
            <v-combobox label="Пользователь" 
                        v-model="item.UF_UID"
                        :items="users">
                <template v-slot:item="{index, item }">
                    <v-list-item :key="'user' + index">
                        {{ item }}
                    </v-list-item>
                </template>
            </v-combobox>
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
    name: 'WpFrmEmployee',
    mixins: [ mxForm ],
    async fetch(){
        return Promise.all([
            this.$store.dispatch("data/list", "users")
        ]);
    },
    data(){
        return {
            item: {},
            errs: {
                name: false
            }
        };
    },
    computed: {
        users(){
            return this.$store.state.data.users;
        }
    },
    methods: {
        get(q, v){
            switch(q){
                case 'uname':
                    console.log(v);
                    return 'UNAME';
            }
        },
        validate(){
            if ( empty(this.item.UF_NAME) ){
                this.errs["name"] = true;
                return false;
            }
            return true;
        },
        async save(){
            try {
                await this.$store.dispatch("data/upd", {employees: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save
    }
}
</script>