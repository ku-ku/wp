<template>
    <v-container fluid>
        <v-data-table :headers="headers"
                      :items="employees"
                      :items-per-page="30"
                      dense
                      single-select
                      item-key="ID"
                      :footer-props="{itemsPerPageText:'строк/стр.'}"
                      :loading="$fetchState.pending"
                      :value="selected"
                      v-on:click:row="selected = [$event]">
        <template v-slot:top>
            <v-toolbar flat>
                <v-badge :content="get('count')">Сотрудники</v-badge>
                <v-spacer></v-spacer>
                <wp-search-field v-on:filter="s = $event" />
                <v-btn small outlined color="secondary"
                       v-on:click="edit">
                       Добавить&nbsp;<v-icon small>mdi-plus</v-icon>
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
        <template v-slot:item.UF_ADDED="{ item }">
            {{ get('dt', item.UF_ADDED) }}
        </template>
        <template v-slot:item.UF_END="{ item }">
            {{ get('dt', item.UF_END) }}
        </template>
    </v-data-table>
    <wp-dialog ref="dlg" 
               :mode="DIA_MODES.emp" 
               v-on:change="change" />
</v-container>
    
</template>
<script>
import { DIA_MODES, empty } from "~/utils/";
import WpDialog from "~/components/WpDialog.vue";
import WpSearchField from "~/components/WpSearchField.vue";
import moment from "moment";

export default {
    name: 'WpEmployees',
    components: {
        WpDialog,
        WpSearchField
    },
    async fetch(){
        try {
            this.all = await this.$store.dispatch("data/list", "employees");
        } catch(e){
            this.all = [];
            console.log('ERR (Employees)', e);
        }
    },
    data(){
        return {
            DIA_MODES,
            all: [],
            selected: [],
            s: null,
            headers: [
                { text: 'ФИО', value: 'UF_EMPNAME' },
                { text: 'Подразделение', value: 'DVS_NAME' },
                { text: 'Должность', value: 'STAFF_NAME' },
                { text: 'Дата приема', value: 'UF_ADDED' },
                { text: 'Дата увольнения', value: 'UF_END' },
                { text: '', value: 'actions', sortable: false, width: "7rem", cellClass: "text-center" }
            ]
        };
    },
    computed: {
        employees(){
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
        get(q, v){
            switch(q){
                case "count":
                    return this.employees?.length;
                case "dt":
                    return (!!v) ? moment(v).format('DD.MM.YYYY') : null;
            }
            return false;
        },
        edit(e){
            console.log('editing', e);
            this.selected = [e];
            this.$refs["dlg"].open(e);
        },
        async del(e){
            if (!window.confirm(`ВНИМАНИЕ! Удалить "${e.UF_NAME}"?`)){
                return;
            }
            try {
                await this.$store.dispatch("data/rm", {employees});
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
            const id = item.ID;
            (async()=>{
                try {
                    await this.$fetch();
                    this.$nextTick(()=>{
                        const n = this.all.findIndex( e => e.ID === id );
                        if ( n > -1 ){
                            this.selected = [this.all[n]];
                        }
                    });
                } catch(e){
                    console.log('ERR (change)', e);
                }
            })();
        }
    }
};
</script>