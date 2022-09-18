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
               v-on:click="gotoday">
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
            class="mx-2"
            label="Вид"
            style="max-width:14rem;">
        </v-select>
        <v-btn color="secondary lighten-3"
               icon
               v-on:click="_fetch()">
            <v-icon v-bind:class="{'mdi-spin': loading}">mdi-refresh</v-icon>
        </v-btn>
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
                :weekdays="[1, 2, 3, 4, 5, 6, 0]"
                v-on:click:date="viewDay"
                v-on:click:event="edit"
                v-on:change="change">
        <template v-slot:event="{ event }">
            <v-tooltip bottom
                       max-width="720"
                       content-class="event-details"
                       :color="event.color">
                <template v-slot:activator="{ on, attrs }">
                    <div v-html="get('title', event)" 
                         v-on="on"
                         v-bind:class="{'red-day': event.red, 'not-complete': !event.ready}">
                    </div>
                </template>    
                <span v-html="event.title"></span>
            </v-tooltip>    
        </template>
        <template v-slot:interval="{date, time}">
            <!-- TODO: v-list>
                <v-list-item v-for="e in events(time)"
                             :key="'ev-' + e.id">
                    <span v-html="e.title"></span>
                </v-list-item>
            </v-list-->
            <v-btn x-small 
                   elevation="0"
                   color="white"
                   fab
                   right
                   absolute
                   v-on:click="addAction(date+'T'+time)">
                   <v-icon small>mdi-plus</v-icon>
            </v-btn>
        </template>
        <template v-slot:day="{ date }">
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
    <wp-dialog v-if="dialog"
               ref="dlgAct" :mode="DIA_MODES.action" 
               v-on:change="_fetch" />
    <wp-dialog v-if="dialog"
               ref="dlgRed" :mode="DIA_MODES.reday" 
               v-on:change="_fetch" />
</v-container>
</template>
<script>
import { mapState } from 'vuex';
import $moment from "moment";
$moment.locale('ru');
import { DIA_MODES } from "~/utils";


export default {
    name: 'WpCalendar',
    fetchOnServer: false,
    data(){
        return {
            DIA_MODES,
            loading: false,
            value: '',
            type: 'month',
            types: [{name: 'Месяц', id:'month'}, {name: 'Неделя', id:'week'}, {name:'День', id:'day'}],
            all: null,
            dialog: false
        };
    },
    computed: {
        ...mapState({
            period: state => state.period,
            division: state => state.data.division
        }),
        imp(){
            return this.$route.query.imp;
        }
    },
    methods:{
        _fetch(){
            this.loading = true;
            var acts = [];
            
            const _get = async q => {
                try {
                    const a = await this.$store.dispatch("data/list", q);
                    acts = acts.concat(a);
                } catch(e){
                    console.log('ERR (calendar)', e);
                    $nuxt.msg({text: 'Ошибка получения списка мероприятий'});
                } finally {
                    if ("acts" === q){
                        setTimeout(()=>{_get("reds");}, 300);
                    } else {
                        this.all = acts;
                        this.loading = false;
                    }
                }
            };  //_get
            
            _get("acts");
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
                case 'title':
                    return ((!!v.red) ? '<i aria-hidden="true" class="v-icon notranslate mdi mdi-flag-variant" style="font-size: 12px;"></i>&nbsp;' : '')
                           + v.title;
            }
        },
        gotoday(){
            this.type = 'day';
            this.$nextTick(()=> this.value='');
        },
        change({start, end}){
            this.$store.commit("set", { period: {
                                            start: [start.year, start.month-1, start.day],
                                            end: [end.year, end.month-1, end.day, 23, 59, 59]
            }});
            //reset for reload
            this.$store.commit("data/set", {acts: null, reds: null});
            this._fetch();
        },
        events(at){
            const _FMT = "YYYY-MM-DD HH:mm:ss";
            if (!!this.all){
                const colors = ['blue', 'indigo', 'brown', 'cyan', 'green', 'orange', 'teal darken-3'];
                const _color = ()=>{
                    return colors[Math.floor((colors.length + 1) * Math.random())];
                };
                const dvs = this.division;
                const events =  this.all.map( a => {
                    const e = {
                        id: a.ID,
                        name: a.UF_TEXT,
                        time: $moment(a.UF_ADT).format("HH:mm"),
                        start: $moment(a.UF_ADT).format(_FMT),
                        color: (1==a.UF_RED) ? "" : _color(),
                        timed: !!a.UF_DAYATTR,
                        red:   (1==a.UF_RED),
                        ready: (1==a.UF_READY),
                        dvs:   a.UF_DVS
                    };
                    const tm = $moment(a.UF_ADT).format("HH:mm");
                    e.title = e.name;
                    if ("00:00" !== tm){
                        e.title += `<div class="time">${ tm }</div>`;
                    } else if (!e.red) {
                        e.start = $moment(a.UF_ADT).add(9, 'hours').format(_FMT);
                    }
                    if ( 
                            (!e.red)
                         && (!e.ready)
                       ) {
                             e.color = 'grey darken-1';
                    }
                    
                    return e;
                }).filter( e => {
                    return (!dvs)||(dvs.ID < 1)||(e.red)||(e.dvs==dvs.ID);
                });
                console.log('events', events);
                if (!!at){
                    return events.filter( e => e.time === at );
                }
                return events;
            } else {
                return [];
            }
        },
        addAction(at){
            
            var item = {ID: -1, UF_ADT: $moment(at).toDate()};
            this.dialog = true;
            this.$nextTick(()=>{
                this.$refs.dlgAct.open(item);
            });
        },
        addRed(at){
            var item = {ID: -1, UF_ADT: at};
            this.dialog = true;
            this.$nextTick(()=>{
                this.$refs.dlgRed.open(item);
            });
        },
        edit({ event }){
            const n = this.all.findIndex( a => a.ID === event.id );
            var item = n < 0 ? null : this.all[n];
            this.dialog = true;
            this.$nextTick(()=>{
                this.$refs[ (1==item.UF_RED) ? "dlgRed" : "dlgAct" ].open(item);
            });
        },
        async doimp(){
            if (this.loading){
                return false;
            }
            const per = this.period;
            console.log('import at', per.start);
            $nuxt.msg({text:'Иморт данных...', type:'primary'});
            this.loading = true;
            try {
                const res = await $nuxt.api("imp", {
                    mn: per.start.get("month") + 1,
                    yr: per.start.get("year")
                });
                console.log('imp', res);
                this._fetch();
            } catch(e){
                console.log('ERR (imp)', e);
            } finally {
                this.loading = false;
                $nuxt.msg();
                this.$router.replace({name: 'calendar', query:{}});
            }
        },
        viewDay({ date }){
            console.log('set dt', date);
            this.value = date;
            this.type = 'day';
        }
    },
    watch: {
        imp(val){
            console.log('imp', val);
            if (!!val){
                this.doimp();
            }
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
.v-event, .v-event-timed {
    & .red-day{
        background: #fff;
        color: $red-color
    }
    & .not-complete{
        background: var(--v-primary-lighten5);
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