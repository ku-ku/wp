<template>
    <v-dialog content-class="wp-dialog"
              v-model="show"
              eager
              scrollable
              max-width="800">
        <v-card tile>
            <v-toolbar dark
                       dense
                       color="primary">
                <v-icon small>{{has('add') ? 'mdi-plus':'mdi-file-document-edit'}}</v-icon>&nbsp;
                {{ title() }}
                <wp-search-field v-if="needs.searchable" 
                                 class="ml-3"
                                 v-on:filter="onsearch" />
                <v-spacer />
                <v-btn small text v-on:click="show = false">
                    <v-icon small>mdi-close</v-icon>
                </v-btn>
            </v-toolbar>
            <v-card-text class="pt-5">
                <component v-bind:is="component"
                           ref="form"
                           v-on:success="success">
                </component>
            </v-card-text>
            <v-card-actions>
                <v-tooltip left>
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn small tile 
                               color="primary"
                               :outlined="!fixed"
                               v-bind="attrs"
                               v-on="on"
                               @click="fix"
                               v-show="mode===DIA_MODES.action">
                            <v-icon small>{{fixed ? 'mdi-lock-check' : 'mdi-lock-open-outline'}}</v-icon>
                        </v-btn>
                    </template>
                    <span>Закрепить значения</span>
                </v-tooltip>
                <v-btn small tile color="primary"
                       :loading="loading"
                       v-on:click="save"
                       class="mx-3">
                    <v-icon small>mdi-file-send-outline</v-icon>сохранить
                </v-btn>
                <v-btn small tile outlined color="secondary"
                       :depressed="fixed"
                       v-on:click="show = false">
                    <v-icon small>mdi-close</v-icon>закрыть
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
import { DIA_MODES, empty } from "~/utils/";
import WpFrmAction from "~/components/WpFrmAction.vue";
import WpFrmRed from "~/components/WpFrmRed.vue";
import WpFrmDivision from "~/components/WpFrmDivision.vue";
import WpFrmUser from "~/components/WpFrmUser.vue";
import WpFrmStaff from "~/components/WpFrmStaff.vue";
import WpFrmEmployee from "~/components/WpFrmEmployee.vue";
import WpSelUsers from "~/components/WpSelUsers.vue";
import WpSelDvss from "~/components/WpSelDvss.vue";
import WpSearchField from "~/components/WpSearchField.vue";

const DIA_FORMS = {};
DIA_FORMS[DIA_MODES.action] = WpFrmAction;
DIA_FORMS[DIA_MODES.reday]  = WpFrmRed;
DIA_FORMS[DIA_MODES.dvs]    = WpFrmDivision;
DIA_FORMS[DIA_MODES.user]   = WpFrmUser;
DIA_FORMS[DIA_MODES.staff]  = WpFrmStaff;
DIA_FORMS[DIA_MODES.emp]    = WpFrmEmployee;
DIA_FORMS[DIA_MODES.emplist]= WpSelUsers;
DIA_FORMS[DIA_MODES.dvslist]= WpSelDvss;

export default {
    name: 'WpDialog',
    components: {
        WpFrmAction,
        WpFrmRed,
        WpFrmDivision,
        WpFrmUser,
        WpFrmStaff,
        WpFrmEmployee,
        WpSelUsers,
        WpSearchField
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
            fAdd: false,
            fixed: false,
            needs: {
                searchable: false,
                title: undefined
            }
        };
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
    provide(){
        const self = this;
        const needs = (need, val)=>{
            self.needs[need] = val;
        };

        return {
            needs
        };
    },
    methods: {
        title(){
            if (empty(this.needs.title)){
                return (this.fAdd) ? 'Новая запись' : 'Редактирование';
            } else {
                return this.needs.title;
            }
        },
        has(q){
            switch(q){
                case "add":
                    return this.fAdd;
                case "searchable":
                    return !!this.needs.searchable;
            }
            return false;
        },  //has
        /**
         * 
         * @param {Object?} item for editing
         */
        open(item){
            var item = (!!item) ? item : {ID: -1};
            const f = this.$refs["form"];
            this.show = (new Date()).getTime();
            f.use(item);
            this.fAdd = f.has('add');
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
        },
        onsearch(s){
            this.$refs["form"].set('search', s);
        },
        fix(){
            this.fixed = this.$refs["form"].set('fix');
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