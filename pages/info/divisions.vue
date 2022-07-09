<template>
<v-container>
    <v-data-table :headers="headers"
                  :items="all"
                  :items-per-page="30"
                  single-select>
        <template v-slot:top>
            <v-toolbar flat>
                <v-badge :content="get('count')">Структура</v-badge>
                <v-spacer></v-spacer>
                <v-btn small outlined color="secondary"
                       v-on:click="edit">
                       Добавить подразделение&nbsp;<v-icon small>mdi-plus</v-icon>
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
    <wp-dialog ref="dlg" :mode="DIA_MODES.dvs" />
</v-container>
</template>
<script>
import { DIA_MODES } from "~/utils/";
import WpDialog from "~/components/WpDialog.vue";

export default {
    name: 'WpDivisions',
    comments:{
        WpDialog
    },
    async asyncData({store}){
        return {
            all: await store.dispatch("data/list", "divisions")
        }
    },
    data(){
        return {
            DIA_MODES,
            headers: [
                { text: 'Код', value: 'UF_CODE' },
                { text: 'Наименование', value: 'UF_NAME' },
                { text: 'Активно', value: 'UF_ACTIVE' },
                { text: 'Порядок', value: 'UF_SORT' },
                { text: '', value: 'actions', sortable: false, width: "7rem", cellClass: "text-center" }
            ]
        };
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
            console.log('edit', dvs);
            this.$refs["dlg"].open(dvs);
        },
        del(dvs){
            console.log('del', dvs);
            if ( confirm('Подтвердите удаление для "' + dvs.UF_NAME + '"') ){

            }
        }
    }
}
</script>
<style lang="scss" scoped>
    table > thead > tr > th {
        vertical-align: top;
    }
</style>