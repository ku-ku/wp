<template>
<v-form>
    <v-row>
        <v-col cols="4">
            <wp-date-input label="Дата, время проведения"
                            :value="item.regDt"
                            v-on:change="set('regDt', $event)">
            </wp-date-input>
        </v-col>
        <v-col cols="8">
            <v-autocomplete dense
                            full-width
                            label="Подразделение"
                            no-data-text="нет данных"
                            v-model="item.DVS"
                            hide-no-data
                            item-value="ID"
                            clearable
                            required
                            hide-details
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
                v-model="item.grAttr"
                label="Согласно графику"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.dayAttr"
                label="В течении дня"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.specAttr"
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
                        v-model="item.text"></v-textarea>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-autocomplete dense
                            full-width
                            no-data-text="нет данных"
                            v-model="item.PLACE"
                            hide-no-data
                            item-value="ID"
                            clearable
                            required
                            hide-details
                            label="Место проведения"
                            :items="places">
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-autocomplete dense
                            full-width
                            label="Руководитель"
                            no-data-text="нет данных"
                            :items="employees">
            </v-autocomplete>
            <v-row>
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
        <v-col cols="6">
            <v-autocomplete v-model="item.status"
                            dense
                            full-width
                            label="Статус мероприятия"
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
        <v-col cols="6">
            <v-autocomplete dense
                            full-width
                            label="Отметка о проведении"
                            no-data-text="нет данных"
                            :items="places">
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-checkbox
                v-model="item.wwwAttr"
                label="Публиковать в www"
                color="green accent-4"
                value="1"
                hide-details>
            </v-checkbox>
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
                DVS: null,
                regDt: null,
                grAttr: 0,
                dayAttr: 0,
                specAttr: 0,
                wwwAttr: 0
            }
        };
    },
    created(){
        this.$fetch();
    },
    methods: {
        set(q, val){
            console.log('set', q, val);
            this[q] = val;
        },
        filterByName(item, s){
            if ( empty(s) || (s.length < 2) ){
                return true;
            }
            const re = new RegExp('(' + s + ')+', 'gi');
            return re.test(item.hasOwnProperty("LAST_NAME") ? item.LAST_NAME : item.UF_NAME);
        },
    }   //methods
}
</script>