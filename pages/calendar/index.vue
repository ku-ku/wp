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
            {{get('today')}}
            <div class="text-muted">сегодня</div>
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
                :events="events()"
                :type="type"
                v-on:click:event="edit">
        <template v-slot:event="{ event }">
            <v-tooltip bottom
                       max-width="720"
                       content-class="event-details"
                       :color="event.color">
                <template v-slot:activator="{ on, attrs }">
                    <div v-html="event.title" 
                         v-on="on"
                         v-bind:class="{'red-day': event.red}">
                    </div>
                </template>    
                <span v-html="event.title"></span>
            </v-tooltip>    
        </template>
        <template v-slot:day="{ past, date }">
            <v-menu dark offset-y color="primary">
                <template v-slot:activator="{ on, attrs }">
                    <v-btn v-on="on"
                           x-small 
                           elevation="0"
                           fab
                           absolute
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
    <wp-dialog ref="dlgAct" :mode="DIA_MODES.action" 
               v-on:change="$fetch" />
    <wp-dialog ref="dlgRed" :mode="DIA_MODES.reday" 
               v-on:change="$fetch" />
</v-container>
</template>
<script>
import $moment from "moment";
import { DIA_MODES } from "~/utils";

export default {
    name: 'WpCalendar',
    async fetch(){
        try {
            var all = await this.$store.dispatch("data/list", "acts");
            this.all = all.concat(
                            await this.$store.dispatch("data/list", "reds")
                    );
        } catch(e){
            this.all = [];
            console.log('ERR (calendar)', e);
            $nuxt.msg({text: 'Ошибка получения списка мероприятий'});
        }
    },
    data(){
        return {
            DIA_MODES,
            value: '',
            type: 'month',
            types: [{name: 'Месяц', id:'month'}, {name: 'Неделя', id:'week'}, {name:'День', id:'day'}],
            all: null
        };
    },
    mounted(){
        this.$fetch();
    },
    methods:{
        log(e){
            console.log(e);
        },
        get(q, v){
            switch(q){
                case 'today':
                    return $moment().format('DD.MM.YYYY');
                case 'event':
                    const tm = $moment(v.start).format("HH:mm");
                    var s = v.name;
                    if ("00:00" !== tm){
                        s += `<div class="time">${ tm }</div>`;
                    }
                    return s;
            }
        },
        events(){
            const _FMT = "YYYY-MM-DD HH:mm:ss";
            if (!!this.all){
                const colors = ['blue', 'indigo', 'deep-purple', 'cyan', 'green', 'orange', 'grey darken-1'];
                const _color = ()=>{
                    return colors[Math.floor((colors.length + 1) * Math.random())];
                };
                return this.all.map( a => {
                    const e = {
                        id: a.ID,
                        name: a.UF_TEXT,
                        start: $moment(a.UF_ADT).format(_FMT),
                        color: (1==a.UF_RED) ? "" : _color(),
                        timed: !!a.UF_DAYATTR,
                        red: 1==a.UF_RED
                    };
                    const tm = $moment(a.UF_ADT).format("HH:mm");
                    e.title = e.name;
                    if ("00:00" !== tm){
                        e.title += `<div class="time">${ tm }</div>`;
                    }
                    return e;
                });
            } else {
                return [];
            }
        },
        addAction(at){
            var item = {ID: -1, UF_ADT: at};
            this.$refs.dlgAct.open(item);
        },
        addRed(at){
            var item = {ID: -1, UF_ADT: at};
            this.$refs.dlgRed.open(item);
        },
        edit({event}){
            const n = this.all.findIndex( a => a.ID === event.id );
            var item = n < 0 ? null : this.all[n];
            this.$refs[ (1==item.UF_RED) ? "dlgRed" : "dlgAct"].open(item);
        }
    }
}
</script>
<style lang="scss">
.v-toolbar.wp-calendar-bar {
    & .v-btn{
        &__content{
            font-size: 0.85rem;
            flex-direction: column;
            line-height: 1.125;
            color:var(--v-primary-base);
            & .text-muted {
                font-size: 0.55rem;
            }
        }
    }
}
.v-event {
    & .red-day{
        background: #fff;
        color: #D50000;
    }
}
.v-event-timed{
    & .time{
        display: none;
    }
}
.event-details > span{
    display: flex;
    flex: 1 1 auto;
    align-items: center;
    flex-direction: row-reverse;
    justify-content: space-between;
    & .time{
        font-size: 1.25rem;
        display: block;
        margin-right: 0.5rem;
        padding-right: 0.5rem;
        border-right: 1px solid #fff;
    }
}

.v-calendar-weekly{
    &__day{
        & .v-btn--fab.v-btn--absolute{
            top: 0;
            right: 0;
        }
    }
}
</style>