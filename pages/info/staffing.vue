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

var hTimer = false;

export default {
    name: 'WpStaffing',
    comments: {
        WpDialog
    },
    async asyncData({store}) {
        var all;
        try {
            all = await store.dispatch("data/list", "staffing");
        } catch(e){
            all = [];
            console.log('ERR (Staffing)', e);
        }
        return { all };
    },
    data(){
        return {
            DIA_MODES,
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
        del(stf){

        },
        filtering(s){
            if (!!hTimer){
                clearTimeout(hTimer);
            }
            hTimer = setTimeout(()=>{
                hTimer = false;
                this.s = s;
            }, 500);
        },
        change(item){
            console.log('change', item);
            const n = this.all.findIndex( e => e.ID === item.ID);
            if ( n > -1 ){
                this.all[n] = item;
            } else {
                this.all.push(item);
            }
        }
    }
    
};
</script>