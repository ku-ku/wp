<template>
<v-form>
    <v-row>
        <v-col cols="4">
            <v-text-field label="Login" v-model="item.LOGIN"></v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-text-field label="Фамилия" v-model="item.LAST_NAME"></v-text-field>
        </v-col>
        <v-col cols="4">
            <v-text-field label="Имя" v-model="item.NAME"></v-text-field>
        </v-col>
        <v-col cols="4">
            <v-text-field label="Отчество" v-model="item.SECOND_NAME"></v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-text-field label="e-mail"  v-model="item.EMAIL" type="email"></v-text-field>
        </v-col>
        <v-col cols="4">
            <v-text-field label="Телефон" v-model="item.PERSONAL_PHONE" type="tel"></v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-checkbox
                v-model="item.plnAttr"
                label="Разрешить планирование"
                value="1"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';

export default{
    name: 'WpFrmUser',
    mixins: [ mxForm ],
    data(){
        return {
            item:{}
        };
    }
}
</script>