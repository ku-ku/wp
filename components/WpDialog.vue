<template>
    <v-dialog content-class="wp-dialog"
              v-model="show"
              max-width="800">
        <v-toolbar dark
                   color="primary">
            <v-icon small>{{has('add') ? 'mdi-plus':'mdi-file-document-edit'}}</v-icon>&nbsp;
            {{has('add') ? 'Новая запись' : 'Редактирование'}}
            <v-spacer />
            <v-btn small text v-on:click="show = false">
                <v-icon small>mdi-close</v-icon>
            </v-btn>
        </v-toolbar>
        <v-card tile class="pt-5">
            <v-card-text>
                <component v-bind:is="component"
                           ref="form"
                           v-on:success="success">
                </component>
            </v-card-text>
            <v-card-actions>
                    <v-btn small tile color="primary"
                           :loading="loading"
                           v-on:click="save">
                        <v-icon small>mdi-file-send-outline</v-icon>сохранить
                    </v-btn>
                    <v-btn small tile outlined color="secondary"
                           v-on:click="show = false">
                        <v-icon small>mdi-close</v-icon>закрыть
                    </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
import { DIA_MODES } from "~/utils/";
import WpFrmAction from "~/components/WpFrmAction.vue";
import WpFrmRed from "~/components/WpFrmRed.vue";
import WpFrmDivision from "~/components/WpFrmDivision.vue";
import WpFrmUser from "~/components/WpFrmUser.vue";
import WpFrmStaff from "~/components/WpFrmStaff.vue";
import WpFrmEmployee from "~/components/WpFrmEmployee.vue";

const DIA_FORMS = {};
DIA_FORMS[DIA_MODES.action] = WpFrmAction;
DIA_FORMS[DIA_MODES.reday]  = WpFrmRed;
DIA_FORMS[DIA_MODES.dvs]    = WpFrmDivision;
DIA_FORMS[DIA_MODES.user]   = WpFrmUser;
DIA_FORMS[DIA_MODES.staff]  = WpFrmStaff;
DIA_FORMS[DIA_MODES.emp]    = WpFrmEmployee;


export default {
    name: 'WpDialog',
    components: {
        WpFrmAction,
        WpFrmRed,
        WpFrmDivision,
        WpFrmUser,
        WpFrmStaff,
        WpFrmEmployee
    },
    props: {
        mode: {
            type: Number,
            required: true,
            default: DIA_MODES.none
        }
    },
    data(){
        return {
            DIA_MODES,
            show: false,
            id: -1
        }
    },
    computed: {
        component(){
            return DIA_FORMS[this.mode];
        },
        //TODO:
        loading(){
            return this.$refs["form"]?.loading;
        }
    },
    methods: {
        has(q){
            switch(q){
                case "add":
                    return this.$refs["form"]?.has("add");
            }
            return false;
        },  //has
        /**
         * 
         * @param {Object?} item for editing
         */
        open(item){
            this.show = (new Date()).getTime();
            this.$nextTick(()=>{
                this.$refs["form"].use(item);
                const inp = $(this.$el).find("input").get(0);
                if ( (!!inp) && (typeof inp.focus !== "undefined") ){
                    inp.focus();
                }
            });
        },
        save(){
            const f = this.$refs["form"];
            if ( f.validate() ){
                f.save();
            }
        },
        success(item){
            this.$emit('change', item);
            this.show = false;
        }
    }
}
</script>
<style lang="scss" scoped>
    .v-card{
        &__text{
            padding-top: 2rem;
        }
        &__actions{
            justify-content: flex-end;
        }
    }
</style>