<template>
<v-container class="mt-5">
    <v-toolbar class="wp-calendar-bar">
        <v-btn icon
            class="ma-2"
            @click="$refs.calendar.prev()">
            <v-icon>mdi-chevron-left</v-icon>
        </v-btn>
        <v-btn outlined dark color="secondary lighten-3"
               style="min-width: 8rem"
               v-on:click="value=''">
            Сегодня
            <div class="text-muted">
                {{get('today')}}
            </div>
        </v-btn>
        <v-select
            v-model="type"
            :items="types"
            item-text="name"
            item-value="id"
            dense
            outlined
            hide-details
            class="ma-2"
            label="Вид">
        </v-select>
        <v-spacer></v-spacer>
        <v-btn
            icon
            class="ma-2"
            @click="$refs.calendar.next()">
            <v-icon>mdi-chevron-right</v-icon>
        </v-btn>        
    </v-toolbar>
    <v-calendar locale="ru" 
                ref="calendar"
                v-model="value"
                class="mt-3"
                color="primary"
                :type="type">
        <template v-slot:day="{ past, date }">
            <v-menu dark offset-y color="primary">
                <template v-slot:activator="{ on, attrs }">
                    <v-btn icon small v-on="on"
                           v-bind="attrs">
                           <v-icon small>mdi-plus</v-icon>
                    </v-btn>
                </template>
                <v-list dense>
                    <v-list-item v-on:click="addAction(date)">
                        <v-list-item-icon><v-icon small>mdi-bank-plus</v-icon></v-list-item-icon>
                        <v-list-item-title>Добавить мероприятие...</v-list-item-title>
                    </v-list-item>
                    <v-divider />
                    <v-list-item v-on:click="addRed(date)">
                        <v-list-item-icon><v-icon small>mdi-calendar-plus</v-icon></v-list-item-icon>
                        <v-list-item-title>Добавить праздничный день...</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </template>
    </v-calendar>
    <wp-dialog ref="dlgAct" :mode="DIA_MODES.action" />
    <wp-dialog ref="dlgRed" :mode="DIA_MODES.reday" />
</v-container>
</template>
<script>
import $moment from "moment";
import { DIA_MODES } from "~/utils";

export default {
    name: 'WpCalendar',
    async asyncData({store, params}) {
        
    },
    data(){
        return {
            DIA_MODES,
            value: '',
            type: 'month',
            types: [{name: 'Месяц', id:'month'}, {name: 'Неделя', id:'week'}, {name:'День', id:'day'}],
        }
    },
    methods:{
        get(q){
            switch(q){
                case 'today':
                    return $moment().format('DD.MM.YYYY');
            }
        },
        addAction(at){
            var item = {ID: -1, regDt: at};
            this.$refs.dlgAct.open(item);
        },
        addRed(at){
            var item = {ID: -1, regDt: at};
            this.$refs.dlgRed.open(item);
        }
    }
}
</script>
<style lang="scss">
.v-toolbar.wp-calendar-bar {
    & .v-btn{
        &__content{
            font-size: 0.8rem;
            flex-direction: column;
            line-height: 1.125;
            & .text-muted {
                font-size: 0.6rem;
                color:var(--v-primary-base);
            }
        }
    }
}
</style>