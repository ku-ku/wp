<template>
    <v-dialog content-class="wp-dialog"
              v-model="show"
              max-width="800">
        <v-toolbar dark
                   color="primary"
                   dense>
            <v-icon>{{has('add') ? 'mdi-calendar-plus':'mdi-calendar-text'}}</v-icon>&nbsp;
            {{has('add') ? 'Новая запись' : 'Редактирование'}}
            <v-spacer />
            <v-btn small icon v-on:click="show = false">
                <v-icon>mdi-close</v-icon>
            </v-btn>
        </v-toolbar>
        <v-card tile>
            <v-card-text>
                <component v-bind:is="component"
                           ref="form">
                </component>
            </v-card-text>
            <v-card-actions>
                    <v-btn small tile color="primary">
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
            }
            return null;
        }
    },
    methods: {
        has(q){
            switch(q){
                case "add":
                    return (this.id < 1);
            }
            return false;
        },  //has
        open(mode, id){
            this.mode = mode,
            this.id = id;
            this.show = (new Date()).getTime();
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