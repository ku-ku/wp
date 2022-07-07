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
        <v-card tile>
            <v-card-text>
                <component v-bind:is="component"
                           ref="form">
                </component>
            </v-card-text>
            <v-card-actions>
                    <v-btn small tile color="primary"
                           :loading="loading">
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

export default {
    name: 'WpDialog',
    components: {
        WpFrmAction
    },
    data(){
        return {
            DIA_MODES,
            mode: DIA_MODES.none,
            show: false,
            id: -1
        }
    },
    computed: {
        component(){
            switch(this.mode){
                case DIA_MODES.action:
                    return WpFrmAction;
                case DIA_MODES.reday:
                    return WpFrmRed;
                case DIA_MODES.dvs:
                    return WpFrmDivision;
                case DIA_MODES.user:
                    return WpFrmUser;
            }
            return null;
        },
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
         * @param {DIA_MODES} mode - use form by
         * @param {Object?} item for editing
         */
        open(mode, item){
            this.mode = mode,
            this.show = (new Date()).getTime();
            this.$nextTick(()=>{
                this.$refs["form"].use(item);
            });
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