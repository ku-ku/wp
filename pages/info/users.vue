<template>
<v-container fluid>
    <v-data-table :headers="headers"
                  :items="users"
                  :items-per-page="30"
                  single-select
                  dense
                  item-key="ID"
                  :footer-props="{itemsPerPageText:'строк/стр.'}"
                  :loading="$fetchState.pending"
                  :value="selected"
                  v-on:click:row="selected = [$event]">
        <template v-slot:top>
            <v-toolbar flat>
                <v-badge :content="get('count')">Пользователи</v-badge>
                <v-spacer></v-spacer>
                <wp-search-field v-on:filter="s = $event" />
                <v-btn small outlined color="secondary"
                       v-on:click="edit">
                       Добавить пользователя&nbsp;<v-icon small>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
        </template>
        <template v-slot:header.actions>
            <div class="text-center"><v-icon>mdi-dots-vertical</v-icon></div>
        </template>
        <template v-slot:item.ACTIVE="{ item }">
            <v-icon v-if="('Y'===item.ACTIVE)" small>mdi-checkbox-outline</v-icon>
        </template>
        <template v-slot:item.WP_PLANNING="{ item }">
            <v-icon v-if="(!!item.WP_PLANNING)" small>mdi-checkbox-outline</v-icon>
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
    <wp-dialog ref="dlg" 
               :mode="DIA_MODES.user" 
               v-on:change="change" />
</v-container>
</template>
<script>
import { DIA_MODES, empty } from "~/utils/";
import WpDialog from "~/components/WpDialog.vue";
import WpSearchField from "~/components/WpSearchField.vue";

var hTimer = false;

export default{
    name: 'WpUsers',
    comments:{
        WpDialog,
        WpSearchField
    },
    data(){
        return {
            DIA_MODES,
            s: null,
            headers: [
                { text: 'Login', value: 'LOGIN',  cellClass: "col-fixed" },
                { text: 'Имя', value: 'NAME' },
                { text: 'Фамилия', value: 'LAST_NAME' },
                { text: 'Отчество', value: 'SECOND_NAME' },
                { text: 'e-mail', value: 'EMAIL' },
                { text: 'Тел.',   value: 'PERSONAL_PHONE' },
                { text: 'А',      value: 'ACTIVE' },
                { text: 'ПЛ.',    value: 'WP_PLANNING' },
                { text: 'Посл.вход', value: 'LAST_LOGIN',  cellClass: "col-fixed"},
                { text: 'Примечание', value: 'PERSONAL_NOTES',  cellClass: "col-fixed" },
                { text: '', value: 'actions', sortable: false, width: "7rem", cellClass: "text-center" }
            ],
            selected: [],
            all: []
        };
    },
    async fetch(){
        try{
            this.all = await this.$store.dispatch("data/list", "users");
        } catch(e){
            this.all = [];
            console.log('ERR (Users)', e);
        }
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
            console.log('edit', user);
            this.selected = [user];
            this.$refs["dlg"].open(user);
        },
        async del(user){
            if ( !window.confirm(`ВНИМАНИЕ! Удалить пользователя "${user.LOGIN}"?`) ){
                return;
            }
            try {
                await this.$store.dispatch("data/rm", {users: user});
                this.$fetch();
            } catch(e){
                console.log('ERR (del)', e);
                $nuxt.msg({type:'warning', text: `ОШИБКА удаления: ${e?.message || 'неизвестная'}`});
            }
        },
        /**
         * Event handle after savig
         */
        change(item){
            console.log('change', item);
            this.$fetch();
        }
    }
}
</script>
