<template>
<v-form v-on:submit.stop.prevent="save"
        class="wp-user">
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
    <v-row align="end">
        <v-col cols="4">
            <v-checkbox
                v-model="item.WP_PLANNING"
                label="Разрешить планирование"
                value="Y"
                color="primary"
                hide-details>
            </v-checkbox>
            <v-checkbox
                v-model="item.WP_MODER"
                label="Модератор плана"
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
        <v-col cols="4">
            <v-btn small outlined tile
                   :disabled="!has('planing')"
                   v-on:click="dvssOpen">
                <v-badge :content="get('divisions')"
                         v-bind:class="{'no-val': !get('divisions')}"
                         color="secondary lighten-4">
                    закрепить подразделения...
                </v-badge>
            </v-btn>
        </v-col>
    </v-row>
    <wp-dialog ref="dlg"
               :mode="DIA_MODES.dvslist" 
               v-on:change="ondvss" />
    
</v-form>
</template>
<script>
import { mxForm } from '~/utils/mxForm.js';
import { DIA_MODES, empty } from "~/utils/";

export default{
    name: 'WpFrmUser',
    mixins: [ mxForm ],
    data(){
        return {
            DIA_MODES,
            item: {},
            errs: {}
        };
    },
    components: {
        WpDialog: () => import("~/components/WpDialog.vue")
    },
    methods: {
        has(q){
            switch(q){
                case "planing":
                    return "Y" === this.item.WP_PLANNING;
            }
        },
        get(q){
            switch(q){
                case "divisions":
                    return this.item.DVSS?.length;
            }
        },
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
        },   //save
        dvssOpen(){
            this.$refs["dlg"].open(this.item.DVSS);
        },
        ondvss(e){
            console.log('dvss', e);
            this.item.DVSS = e;
            this.$forceUpdate();
        }
    }
    
}
</script>
<style lang="scss">
    form.wp-user{
        & .v-badge{
            &.no-val{
                & .v-badge__badge{
                    display: none;
                }
            }
        }
    }
</style>    