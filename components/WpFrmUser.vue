<template>
<v-form v-on:submit.stop.prevent="save">
    <v-row>
        <v-col cols="4">
            <v-text-field v-model="item.LOGIN"
                          label="Login"
                          clearable
                          :error="errs.LOGIN"
                          hide-details
                          required>
            </v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-text-field v-model="item.LAST_NAME"
                          label="Фамилия" 
                          clearable
                          :error="errs.LAST_NAME"
                          hide-details
                          required>
            </v-text-field>
        </v-col>
        <v-col cols="4">
            <v-text-field v-model="item.NAME"
                          label="Имя"
                          clearable
                          :error="errs.NAME"
                          hide-details
                          required>
            </v-text-field>
        </v-col>
        <v-col cols="4">
            <v-text-field v-model="item.SECOND_NAME"
                          label="Отчество" 
                          clearable
                          :error="errs.SECOND_NAME"
                          hide-details>
            </v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-text-field v-model="item.EMAIL" 
                          label="E-mail"
                          type="email"
                          clearable
                          :error="errs.EMAIL"
                          hide-details>
            </v-text-field>
        </v-col>
        <v-col cols="4">
            <v-text-field v-model="item.PERSONAL_PHONE" 
                          label="Телефон"                           
                          type="tel"
                          clearable
                          hide-details>
            </v-text-field>
        </v-col>
    </v-row>
    <v-row>
        <v-col cols="4">
            <v-checkbox
                v-model="item.WP_PLANNING"
                label="Разрешить планирование"
                value="Y"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
        <v-col cols="4">
            <v-checkbox
                v-model="item.ACTIVE"
                label="Активный"
                value="Y"
                color="primary"
                hide-details>
            </v-checkbox>
        </v-col>
    </v-row>
</v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';
import { empty } from '~/utils/';

export default{
    name: 'WpFrmUser',
    mixins: [ mxForm ],
    data(){
        return {
            item: {},
            errs: {}
        };
    },
    methods: {
        validate(){
            const _RQS = ["LOGIN", "LAST_NAME", "NAME"],
                  errs = { n: 0 };
            _RQS.map( r => {
                if ( empty(this.item[r]) ){
                    errs.n++;
                    errs[r] = true;
                }
            });
            
            if (!empty(this.item.EMAIL)){
                if ( !/\S+@\S+\.\S+/i.test(this.item.EMAIL) ){
                    errs.n++;
                    errs["EMAIL"] = true;
                }
            }
            
            this.errs = errs;
            return (errs.n === 0);
        },
        async save(){
            try {
                await this.$store.dispatch("data/upd", {users: this.item});
                this.$emit("success", this.item);
            } catch(e){
                this.$emit("error", e);
            }
            return false;
        }   //save
    }
    
}
</script>