<template>
<v-container fluid>
    <v-data-table :headers="headers"
                  :items="users"
                  :items-per-page="30"
                  single-select
                  dense>
        <template v-slot:top>
            <v-toolbar flat>
                <v-badge :content="get('count')">Пользователи</v-badge>
                <v-spacer></v-spacer>
                <v-text-field v-on:input="filtering" 
                              placeholder="поиск"
                              dense
                              clearable
                              style="max-width:15rem;margin-right:1rem;"
                              hide-details>
                    <template v-slot:append>
                        <v-icon>mdi-magnify</v-icon>
                    </template>
                </v-text-field>
                <v-btn small outlined color="secondary"
                       v-on:click="edit">
                       Добавить пользователя&nbsp;<v-icon small>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
        </template>
        <template v-slot:header.actions>
            <div class="text-center"><v-icon>mdi-dots-vertical</v-icon></div>
        </template>
        <template v-slot:item.actions="{ item }">
            <v-btn small icon v-on:click="edit(item)">
                <v-icon small>mdi-file-document-edit</v-icon>
            </v-btn>
            <v-btn small icon v-on:click="del(item)">
                <v-icon small>mdi-delete</v-icon>
            </v-btn>
        </template>
    </v-data-table>
    <wp-dialog ref="dlg" />
</v-container>
</template>
<script>
import { DIA_MODES, empty } from "~/utils/";
import WpDialog from "~/components/WpDialog.vue";

var hTimer = false;

export default{
    name: 'WpUsers',
    comments:{
        WpDialog
    },
    async asyncData({store}) {
        return {
            all: await store.dispatch("data/list", "users")
        }
    },
    data(){
        return {
            s: null,
            headers: [
                { text: 'Login', value: 'LOGIN',  cellClass: "col-fixed" },
                { text: 'Имя', value: 'NAME' },
                { text: 'Фамилия', value: 'LAST_NAME' },
                { text: 'Отчество', value: 'SECOND_NAME' },
                { text: 'e-mail', value: 'EMAIL' },
                { text: 'Тел.', value: 'PERSONAL_PHONE' },
                { text: 'А', value: 'ACTIVE' },
                { text: 'Посл.вход', value: 'LAST_LOGIN',  cellClass: "col-fixed"},
                { text: 'Примечание', value: 'PERSONAL_NOTES',  cellClass: "col-fixed" },
                { text: '', value: 'actions', sortable: false, width: "7rem", cellClass: "text-center" }
            ]
        };
    },
    computed: {
        users(){
            if (!this.all){
                return [];
            }
            if ( empty(this.s) ){
                return this.all;
            } else {
                const re = new RegExp('(' + this.s + ')+', 'gi');
                return this.all.filter( e => {
                    return re.test(e.LOGIN) 
                        || re.test(e.LAST_NAME)
                        || re.test(e.EMAIL);
                });
            }
        }
    },
    methods: {
        get(q){
            switch(q){
                case "count":
                    return this.users?.length;
            }
            return false;
        },
        edit(user){
            this.$refs["dlg"].open(DIA_MODES.user, user);
        },
        del(user){

        },
        filtering(s){
            if (!!hTimer){
                clearTimeout(hTimer);
            }
            hTimer = setTimeout(()=>{
                hTimer = false;
                this.s = s;
            }, 500);
        }
    }
}
</script>
<style lang="scss">
    .col-fixed {
        max-width: 18rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
<style lang="scss" scoped>
    table > thead > tr > th {
        vertical-align: top;
    }
</style>