<template>
<v-form>
    <v-row>
        <v-col cols="4">
            <wp-date-input label="Дата, время проведения"
                            :value="regDt"
                            v-on:change="set('regDt', $event)">
            </wp-date-input>
        </v-col>
        <v-col cols="8">
            <v-autocomplete dense
                            full-width
                            label="Подразделение"
                            no-data-text="нет данных"
                            :items="divisions">
            </v-autocomplete>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-checkbox
                v-model="grAttr"
                label="Согласно графику"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="dayAttr"
                label="В течении дня"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="specAttr"
                label="Особой важности"
                color="red"
                value="1"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-textarea label="Мероприятие" rows="2"></v-textarea>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-autocomplete dense
                            full-width
                            label="Место проведения"
                            no-data-text="нет данных"
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
                            :items="places">
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
            <v-autocomplete dense
                            full-width
                            label="Статус мероприятия"
                            no-data-text="нет данных"
                            :items="places">
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
                v-model="wwwAttr"
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

export default {
    name: "WpFrmAction",
    mixins: [mxForm],
    async fetch(){
        try {
            this.divisions = await this.$store.dispatch("data/list", "divisions");
        } catch(e){
            console.log('ERR (action)', e);
        }
    },
    data(){
        return {
            error: null,
            id: -1,
            regDt: null,
            grAttr: 0,
            dayAttr: 0,
            specAttr: 0,
            wwwAttr: 0
        }
    },
    methods: {
        set(q, val){
            console.log('set', q, val);
            this[q] = val;
        }
    }   //methods
}
</script>
<style lang="scss" scoped>
</style>