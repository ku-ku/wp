<template>
<v-container>
    <v-data-table :headers="headers"
                  :items="divisions"
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
                <v-badge :content="get('count')">Структура</v-badge>
                <v-spacer></v-spacer>
                <wp-search-field v-on:filter="s = $event" />
                <v-btn small outlined color="secondary"
                       v-on:click="edit">
                       Добавить подразделение&nbsp;<v-icon small>mdi-plus</v-icon>
                </v-btn>
            </v-toolbar>
        </template>
        <template v-slot:header.actions>
            <div class="text-center"><v-icon>mdi-dots-vertical</v-icon></div>
        </template>
        <template v-slot:item.UF_ACTIVE="{ item }">
            <v-icon v-if="(!!item.UF_ACTIVE)" small>mdi-checkbox-outline</v-icon>
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
    <wp-dialog ref="dlg" :mode="DIA_MODES.dvs" 
               v-on:change="change" />
</v-container>
</template>
<script>
import { DIA_MODES, empty } from "~/utils/";
import WpDialog from "~/components/WpDialog.vue";
import WpSearchField from "~/components/WpSearchField.vue";

export default {
    name: 'WpDivisions',
    comments: {
        WpSearchField,
        WpDialog
    },
    data(){
        return {
            DIA_MODES,
            headers: [
                { text: 'Код', value: 'UF_CODE' },
                { text: 'Наименование', value: 'UF_NAME' },
                { text: 'Активно', value: 'UF_ACTIVE', cellClass: "text-center" },
                { text: 'Порядок', value: 'UF_SORT' },
                { text: '', value: 'actions', sortable: false, width: "7rem", cellClass: "text-center" }
            ],
            all: [],
            s: null,
            selected: []
        };
    },
    async fetch(){
        this.s = null;
        this.selected = [];
        try {
            this.all = await this.$store.dispatch("data/list", "divisions");
        } catch(e) {
            this.all = [];
            console.log('ERR (divisions)', e);
        }
    },
    computed: {
        divisions(){
            if (!this.all){
                return [];
            }
            if ( empty(this.s) ){
                return this.all;
            } else {
                const re = new RegExp('(' + this.s + ')+', 'gi');
                return this.all.filter( e => {
                    return ((this.s===e.UF_CODE) || re.test(e.UF_NAME));
                });
            }
        }
    },
    methods: {
        get(q){
            switch(q){
                case "count":
                    return this.all?.length;
            }
            return false;
        },
        edit(dvs){
            this.selected = [dvs];
            this.$refs["dlg"].open(dvs);
        },
        async del(dvs){
            this.selected = [dvs];
            if ( !confirm('Подтвердите удаление для "' + dvs.UF_NAME + '"') ){
                return;
            }
            try {
                await this.$store.dispatch("data/rm", {divisions: dvs});
                this.$fetch();
            } catch(e){
                console.log('ERR (del)', e);
                $nuxt.msg({type:'warning', text: `ОШИБКА удаления: ${e?.message || 'неизвестная'}`});
            }
        },  //del
        change(item){
            this.$fetch();
            this.$nextTick(()=>{
                const n = this.all.findIndex( e => e.ID === item.ID );
                if ( n > -1 ){
                    this.selected = [this.all[n]];
                }
            });
        }
    }
}
</script>
<style lang="scss" scoped>
    table > thead > tr > th {
        vertical-align: top;
    }
</style>