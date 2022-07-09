<template>
<v-form>
    <v-row>
        <v-col cols="12">
            <v-text-field label="Наименование" 
                          v-model="item.UF_NAME"
                          required></v-text-field>
        </v-col>
    </v-row>
    <v-row justify="space-between">
        <v-col cols="auto">
            <v-checkbox
                v-model="item.UF_ACTIVE"
                label="Активно"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="3">
            <v-text-field label="Код" 
                          v-model="item.UF_CODE"
                          required></v-text-field>
        </v-col>
        <v-col cols="3">
            <v-text-field label="Порядок для сортировки" 
                          v-model="item.UF_SORT"
                          required></v-text-field>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';

export default {
    name: 'WpFrmDivision',
    mixins: [ mxForm ],
    data(){
        return {
            item: {}
        };
    }
}
</script>