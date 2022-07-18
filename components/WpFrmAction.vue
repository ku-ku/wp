<template>
<v-form>
    <v-row>
        <v-col cols="4">
            <wp-date-input label="Дата, время проведения"
                            :value="item.UF_ADT"
                            :error="errs.UF_ADT"
                            v-on:change="set('UF_ADT', $event)">
            </wp-date-input>
        </v-col>
        <v-col cols="8">
            <v-autocomplete dense
                            full-width
                            label="Подразделение"
                            no-data-text="нет данных"
                            v-model="item.UF_DVS"
                            hide-no-data
                            item-value="ID"
                            clearable
                            required
                            hide-details
                            :error="errs.UF_DVS"
                            :filter="filterByName"
                            :items="divisions">
                <template v-slot:selection="{ item }">
                    <div class="text-truncate">
                        {{item.UF_CODE}}. {{item.UF_NAME}}
                    </div>    
                </template>
                <template v-slot:item="{ item }">
                    <div class="text-truncate">
                        {{item.UF_CODE}}. {{item.UF_NAME}}
                    </div>    
                </template>
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_GRATTR"
                label="Согласно графику"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_DAYATTR"
                label="В течении дня"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_SPECATTR"
                label="Особой важности"
                color="red"
                value="1"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-textarea label="Мероприятие" rows="2"
                        v-model="item.UF_TEXT"
                        :error="errs.UF_TEXT"></v-textarea>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-combobox dense
                            full-width
                            no-data-text="нет данных"
                            v-model="item.UF_PLACE"
                            hide-no-data
                            item-value="ID"
                            clearable
                            required
                            hide-details
                            label="Место проведения"
                            :items="places"
                            :error="errs.UF_PLACE"
                            >
            </v-combobox>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-autocomplete dense
                            full-width
                            label="Руководитель"
                            no-data-text="нет данных"
                            v-model="item.UF_CHIEF"
                            hide-no-data
                            item-value="ID"
                            item-text="UF_EMPNAME"
                            clearable
                            required
                            hide-details
                            :error="errs.UF_CHIEF"
                            :items="employees">
            </v-autocomplete>
            <v-row class="mt-5">
                <v-col cols="6">
                    <v-btn small block tile>Дополнительно...</v-btn>
                </v-col>
                <v-col cols="6">
                    <v-btn small block tile>Ответственные за подготовку...</v-btn>
                </v-col>
            </v-row>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="2">
            <v-checkbox
                v-model="item.UF_READY"
                label="ГОТОВО"
                color="primary"
                value="1"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.UF_WWWATTR"
                label="Публиковать в www"
                color="green accent-4"
                value="1"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="6">
            <v-autocomplete v-model="item.UF_STATUS"
                            dense
                            full-width
                            label="Отметка о проведении"
                            no-data-text="нет данных"
                            hide-no-data
                            item-value="ID"
                            clearable
                            required
                            hide-details
                            item-text="name"
                            :items="statuses">
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-textarea label="Примечание" rows="2"
                        v-model="item.UF_COMMENTS"></v-textarea>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';
import { DIA_MODES, empty } from "~/utils/";


export default {
    name: "WpFrmAction",
    mixins: [mxForm],
    async fetch(){
        try {
            this.statuses = await this.$store.dispatch("data/list", "statuses");
            this.divisions = await this.$store.dispatch("data/list", "divisions");
            this.employees = await this.$store.dispatch("data/list", "employees");
            this.places = await this.$store.dispatch("data/list", "places");
        } catch(e){
            console.log('ERR (action)', e);
        }
    },
    data(){
        return {
            error: null,
            statuses: null,
            divisions: null,
            employees: null,
            places: null,
            item: {
                ID: -1,
                UF_RED: 0,
                UF_ADT: null,
                UF_DVS: null,
                UF_GRATTR: 0,
                UF_DAYATTR: 0,
                UF_SPECATTR: 0,
                UF_WWWATTR: 0,
                UF_TEXT: null,
                UF_PLACE: null,
                UF_CHIEF: null,
                UF_STATUS: null,
                UF_COMMENTS: null
            },
            errs: {}
        };
    },
    created(){
        this.$fetch();
    },
    methods: {
        set(q, val){
            switch(q){
                default:
                    this.item[q] = val;
                    break;
            }
        },
        filterByName(item, s){
            if ( empty(s) || (s.length < 2) ){
                return true;
            }
            const re = new RegExp('(' + s + ')+', 'gi');
            return re.test(item.hasOwnProperty("LAST_NAME") ? item.LAST_NAME : item.UF_NAME);
        },
        validate(){
            const _RQS = ["UF_ADT", "UF_DVS", "UF_TEXT", "UF_PLACE", "UF_CHIEF"],
                  errs = { n: 0 };
            _RQS.map( r => {
                if ( empty(this.item[r]) ){
                    errs.n++;
                    errs[r] = true;
                }
            });
            this.errs = errs;
            return (errs.n === 0);
        },
        async save(){
            try {
                await this.$store.dispatch("data/upd", {acts: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save
        
    }   //methods
}
</script>