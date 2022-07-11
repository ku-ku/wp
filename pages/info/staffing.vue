<template>
    <v-container>
        <v-data-table :headers="headers"
                      :items="staffs"
                      :items-per-page="30"
                      single-select
                      dense>
        <template v-slot:top>
            <v-toolbar flat>
                <v-badge :content="get('count')">Должности</v-badge>
                <v-spacer></v-spacer>
                <wp-search-field v-on:filter="s = $event" />
                <v-btn small outlined color="secondary"
                       v-on:click="edit">
                       Добавить должность&nbsp;<v-icon small>mdi-plus</v-icon>
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
        <template v-slot:item.UF_DISABLE="{ item }">
            <v-icon v-if="Number(item.UF_DISABLE)>0">mdi-checkbox-outline</v-icon>
        </template>
    </v-data-table>
    <wp-dialog ref="dlg" 
               :mode="DIA_MODES.staff" 
               v-on:change="change" />
</v-container>
    
</template>
<script>
import { DIA_MODES, empty } from "~/utils/";
import WpDialog from "~/components/WpDialog.vue";
import WpSearchField from "~/components/WpSearchField.vue";

export default {
    name: 'WpStaffing',
    comments: {
        WpDialog,
        WpSearchField
    },
    async fetch(){
        try {
            this.all = await this.$store.dispatch("data/list", "staffing");
        } catch(e){
            this.all = [];
            console.log('ERR (Staffing)', e);
        }
    },
    data(){
        return {
            DIA_MODES,
            all: [],
            s: null,
            headers: [
                { text: 'Наименование', value: 'UF_NAME' },
                { text: 'Откл', value: 'UF_DISABLE', cellClass: "col-fixed text-center" },
                { text: '', value: 'actions', sortable: false, width: "7rem", cellClass: "text-center" }
            ]
        };
    },
    computed: {
        staffs(){
            if (!this.all){
                return [];
            }
            if ( empty(this.s) ){
                return this.all;
            } else {
                const re = new RegExp('(' + this.s + ')+', 'gi');
                return this.all.filter( e => {
                    return re.test(e.UF_NAME);
                });
            }
        }   //staffs
    },
    methods: {
        get(q){
            switch(q){
                case "count":
                    return this.staffs?.length;
            }
            return false;
        },
        edit(stf){
            this.$refs["dlg"].open(stf);
        },
        async del(staffing){
            if (!window.confirm(`ВНИМАНИЕ! Удалить должность "${staffing.UF_NAME}"?`)){
                return;
            }
            try {
                await this.$store.dispatch("data/rm", {staffing});
                this.$fetch();
            }catch(e){
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
};
</script>