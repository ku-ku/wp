<template>
<v-form>
    <v-row>
        <v-col cols="4">
            <wp-date-input label="Дата"
                            :value="rdDay"
                            v-on:change="set('rdDay', $event)">
            </wp-date-input>
        </v-col>
        <v-col cols="8">
            <v-checkbox
                v-model="yrAttr"
                label="Ежегодный"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="12">
            <v-textarea label="Наименование" rows="3" v-model="rdName"></v-textarea>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';

export default {
    name: 'WpFrmRed',
    mixins: [ mxForm ],
    data(){
        return {
            id: -1,
            yrAttr: 0,
            rdDay: null,
        };
    },
    methods: {
        set(q, val){
            console.log('set', q, val);
            this[q] = val;
        }
    }
}
</script>