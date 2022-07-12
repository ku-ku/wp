<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="12">
            <v-autocomplete label="Пользователь" 
                        v-model="item.UF_UID"
                        hide-no-data
                        item-value="ID"
                        clearable
                        :filter="filterUsers"
                        :items="users">
                <template v-slot:item="{item}">
                    <v-list-item :key="'user-' + item.ID">
                        {{item.LAST_NAME}} {{item.NAME}} {{item.SECOND_NAME}} ({{item.LOGIN}})
                    </v-list-item>
                </template>
            </v-autocomplete>
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
        await Promise.all([
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
            return this.$store.state.data.users?.filter( u=> !empty(u.LAST_NAME) );
        }
    },
    methods: {
        empty,
        get(q, v){
            switch(q){
            }
        },
        filterUsers(user, s){
            if ( empty(s) || (s.length < 2) ){
                return true;
            }
            const re = new RegExp('(' + s + ')+', 'gi');
            re.test(user.LAST_NAME);
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