<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="6">
            <wp-date-input label="Дата рег."
                           :value="item.UF_ADDED"
                           v-on:change="set('UF_ADDED', $event)">
            </wp-date-input>
        </v-col>
        <v-col cols="6">
            <wp-date-input label="Дата увольнения"
                           :value="item.UF_END"
                           v-on:change="set('UF_END', $event)">
            </wp-date-input>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-autocomplete label="Пользователь" 
                        v-model="item.UF_UID"
                        hide-no-data
                        item-value="ID"
                        clearable
                        :filter="filterUsers"
                        :items="users()">
                <template v-slot:selection="{ attr, on, item, selected }">
                    {{item.LAST_NAME}} {{item.NAME}} {{item.SECOND_NAME}}
                </template>
                <template v-slot:item="{ item }">
                    <div>{{item.LAST_NAME}} {{item.NAME}} {{item.SECOND_NAME}}</div>
                    <div class="text-muted text-right ml-3">({{item.LOGIN}})</div>
                </template>
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="6">
            <v-autocomplete label="Подразделение" 
                        v-model="item.UF_DVS"
                        hide-no-data
                        item-value="ID"
                        clearable
                        :filter="filterUsers"
                        :items="divisions()">
                <template v-slot:selection="{ attr, on, item, selected }">
                    {{item.UF_CODE}}. {{item.UF_NAME}}
                </template>
                <template v-slot:item="{ item }">
                    {{item.UF_CODE}}. {{item.UF_NAME}}
                </template>
            </v-autocomplete>
        </v-col>
        <v-col cols="6">
            <v-autocomplete label="Должность" 
                        v-model="item.UF_STAFF"
                        hide-no-data
                        item-value="ID"
                        clearable
                        :filter="filterUsers"
                        :items="staffing()">
                <template v-slot:selection="{ attr, on, item, selected }">
                    {{item.UF_NAME}}
                </template>
                <template v-slot:item="{ item }">
                    {{item.UF_NAME}}
                </template>
            </v-autocomplete>
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
        await this.$store.dispatch("data/list", "staffing");
        await this.$store.dispatch("data/list", "divisions");
        await this.$store.dispatch("data/list", "users");
    },
    data(){
        return {
            item: {},
            errs: {
                name: false
            }
        };
    },
    created(){
        this.$fetch();
    },
    methods: {
        empty,
        get(q, v){
            switch(q){
            }
        },
        users(){
                return this.$store.state.data.users?.filter( u => !empty(u.LAST_NAME) )
                           .sort( (u1, u2) => {
                                return u1.LAST_NAME.localeCompare(u2.LAST_NAME);
                            });
        },   //users
        filterUsers(user, s){
            if ( empty(s) || (s.length < 2) ){
                return true;
            }
            const re = new RegExp('(' + s + ')+', 'gi');
            return re.test(user.LAST_NAME);
        },
        staffing(){
            return this.$store.state.data.staffing;
        },
        divisions(){
            return this.$store.state.data.divisions;
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
<style lang="scss">
    .v-list.v-select-list{
        & .v-list-item {
            min-height: 36px;
            align-content: center;
            flex-wrap: nowrap;
            padding-top: 0;
            padding-bottom: 0;
            & .text-muted {
                font-size: 0.75rem;
                color: #78909C;
                max-width: 12rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    }
</style>