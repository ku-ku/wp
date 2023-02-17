<template>
<div>
    <div class="wp-calendar-conte">
        <template v-if="('month'===type)">
            <v-calendar locale="ru" 
                        ref="calendar"
                        v-model="value"
                        color="primary"
                        :events="events()"
                        :type="type"
                        :weekdays="[1, 2, 3, 4, 5, 6, 0]"
                        v-on:click:date="viewDay"
                        v-on:click:event="edit"
                        v-on:change="change"
                        v-on:click:more="more"
                        event-more-text="показать еще {0}"
                        event-overlap-mode="column">
                <template v-slot:event="{ event }">
                    <v-tooltip bottom
                               max-width="720"
                               :color="event.red ? 'red' : 'primary'"
                               content-class="event-details">
                        <template v-slot:activator="{ on, attrs }">
                            <div v-html="get('title', event)" 
                                 v-on="on"
                                 v-bind:class="event.class">
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
        </template>
        <div v-else class="wp-calendar-conte">
            <v-data-table
                      class="events-table"
                      item-key="id"
                      :headers="headers"
                      :items="events(value)"
                      :loading="loading"
                      single-select
                      disable-pagination
                      disable-sort
                      hide-default-footer
                      dense
                      :value="selected"
                      v-on:click:row="selected = [$event]"
                      v-on:dblclick:row="($event, item)=>edit({event: item.item})"
                      no-data-text="нет данных"
                      locale="ru-RU"
                      ref="table">
                    <template v-slot:header.actions>
                        <div class="text-center"><v-icon>mdi-dots-vertical</v-icon></div>
                    </template>
                    <template v-slot:item.adt="{ item }">
                        {{ item.adt.format(item.dayattr ? 'DD.MM.YYYY' : 'DD.MM.YYYY HH:mm').replace(/(00:00)+$/, '') }}
                    </template>
                    <template v-slot:item.name="{ item }">
                        <span v-bind:class="{'red-day': item.red}" 
                              v-html="item.name"></span>
                        <div class="division" v-if="!item.red && !!item.dvsname">
                            {{ item.dvsname }}
                        </div>
                    </template>    
                    <template v-slot:item.ready="{ item }">
                        <v-icon small v-if="!!item.ready" color="blue darken-4">mdi-checkbox-outline</v-icon>
                        <v-icon small v-else>mdi-checkbox-blank-outline</v-icon>&nbsp;/&nbsp;
                        <v-icon small v-if="!!item.www"  color="green accent-4">mdi-checkbox-outline</v-icon>
                        <v-icon small v-else>mdi-checkbox-blank-outline</v-icon> 
                    </template>
                    <template v-slot:item.actions="{ item }">
                        <v-btn small icon v-on:click="edit({event: item})">
                            <v-icon small>mdi-file-document-edit</v-icon>
                        </v-btn>
                        <v-btn small icon v-on:click="del({event: item})">
                            <v-icon small>mdi-delete</v-icon>
                        </v-btn>
                    </template>
                </v-data-table>
                <v-btn fab dark
                       absolute
                       right
                       style="bottom:4rem;"
                       small
                       v-on:click="addAction">
                    <v-icon small>mdi-plus</v-icon>
                </v-btn>
        </div>
    </div>
    <wp-dialog v-if="dialog"
               ref="dlgAct" :mode="DIA_MODES.action" 
               v-on:change="_fetch" />
    <wp-dialog v-if="dialog"
               ref="dlgRed" :mode="DIA_MODES.reday" 
               v-on:change="_fetch" />
</div>
</template>
<script>
import { mapState } from 'vuex';
import $moment from "moment";
$moment.locale('ru');
import { DIA_MODES } from "~/utils";

const WP_SETTS_KEY = "_wp_settings";

const _TB_HEADERS = [
    { text: 'Дата, время', value: 'adt', width: "8rem"},
    { text: 'Мероприятие', value: 'name' },
    { text: 'Готов/www',   value: 'ready', width: "1rem"},
    { text: '', value: 'actions', sortable: false, width: "7rem", cellClass: "text-center" }
];

let _on = null; 

export default {
    name: 'WpCalendar',
    fetchOnServer: false,
    async asyncData({store}){
        //preload data
        try {
            await store.dispatch("data/list", "divisions");
        } catch(e){
            console.log("ERR (data)", e);
        }
    },
    data(){
        return {
            DIA_MODES,
            loading: false,
            types: [{name: 'Месяц', id:'month'}, {name:'День', id:'day'}],
            all: null,
            dialog: false,
            headers: _TB_HEADERS,
            selected: [] /*tb-selected item*/
        };
    },
    created(){
        console.log('calendar', this);
        $nuxt.hidextras();
        if (!_on){
            $nuxt.$off('calendar');
        }
        _on = $nuxt.$on('calendar', e => {
                console.log('calendar', e);
                switch(e.type){
                    case 'month':
                        this.type = 'month';
                        break;
                    case 'prev':
                        this.prev();
                        break;
                    case 'today':
                        this.gotoday();
                        break;
                    case 'refresh':
                        this.$store.commit("data/set", {acts: null, reds: null});
                        this.all = null;
                        this.$nextTick(()=>{ this._fetch(); });
                        break;
                    case 'next':
                        this.next();
                        break;
                }
                return false;
        });
        try {
            var setts = window.localStorage.getItem(WP_SETTS_KEY);
            if (setts){
                setts = JSON.parse(setts);
                this.type = setts.CALENDAR_TYPE || 'month';
                if (this.type !== 'month' ){
                    this.value= $moment(setts.CALENDAR_DATE).toDate();
                    this._fetch();
                }
            }
        }catch(e){}
    },
    mounted(){
        const _adjust = ()=>{
            const conte = $(".wp-calendar-conte");
            if ( conte.length > 0 ){
                conte.css({height: `calc(100vh - ${conte.offset().top + 16}px)`});
            }
        };
        this.$nextTick( _adjust );
        window.addEventListener('resize', _adjust);
    },
    updated(){
        try {
            const setts = JSON.parse(localStorage.getItem(WP_SETTS_KEY)) || {};
            setts.CALENDAR_TYPE = this.type;
            setts.CALENDAR_DATE = $moment(this.value).toDate().getTime();
            window.localStorage.setItem(WP_SETTS_KEY, JSON.stringify(setts));
        }catch(e){}
    },
    computed: {
        ...mapState({
            period: state => state.period,
            division: state => state.data.division
        }),
        divisions(){
            return this.$store.getters['data/divisions'];
        },
        imp(){
            return this.$route.query.imp;
        },
        type: {
            get(){
                return this.$store.state.period.type || 'month';
            },
            set(val){
                this.$store.commit("set", {period: {type: val || 'month'}});
            }
        },
        value: {
            get(){
                return this.$store.state.period.work.toDate();
            },
            set(val){
                console.log('value', val);
                this.$store.commit("set", {period: {work: val}});
            }
        }
    },
    methods:{
        moment: $moment,
        _fetch(){
            this.loading = true;
            this.$nuxt.$loading.start();
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
                        this.$nuxt.$loading.finish();
                    }
                }
            };  //_get
            _get("acts");
        },
        
        get(q, v){
            switch(q){
                case 'day':
                    return $moment(this.value).format('DD.MM.YYYY');
                case 'month':
                    return $moment(this.period.start).format('MMMM');
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
            this.value=new Date();
            this.$forceUpdate();
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
        more(e){
            this.value= new Date(e.year, e.month-1, e.day);
            this.type = 'day';
        },
        events(at){
            const _FMT = "YYYY-MM-DD HH:mm:ss";
            if (!!this.all){
                const dvs = this.division,
                     dvss = this.divisions || [];
             
                const events =  this.all.map( a => {
                    const e = {
                        id:      a.ID,
                        adt:     $moment(a.UF_ADT),
                        name:    a.UF_TEXT,
                        timed:   !!a.UF_DAYATTR,
                        red:     (1 == a.UF_RED),
                        ready:   (1 == a.UF_READY),
                        dvs:     a.UF_DVS,
                        dayattr: (1 == a.UF_DAYATTR),
                        www:     (1 == a.UF_WWWATTR),
                        "class": (1 == a.UF_RED) ? "red-day" : "action"
                    };
                    e.time = e.adt.format("HH:mm");
                    
                    if (e.dayattr){
                        e.adt.set({hour:23, minute:59, second: 59});    //downshift
                    } else if (e.red){
                        e.adt.set({hour:0, minute:0, second: 0});      //up
                    }
                    e.start= e.adt.format(_FMT);
                    
                    e.title = e.name;
                    if ( !e.red && ("00:00" !== e.time) ){
                        e.title += `<div class="time">${ e.time }</div>`;
                    } else if ((!e.red) && (e.dayattr)) {
                        e.title += '<div class="time">дн.</div>';
                        //TODO: nax? e.start = $moment(a.UF_ADT).add(9, 'hours').format(_FMT);
                    }
                    
                    if ( 
                            (!e.red)
                         && (!e.ready)
                       ) {
                        e.class = 'not-ready';
                    } else if (e.dayattr){
                        e.class = 'allday';
                    }
                    
                    if ((!e.red) && (!!e.dvs)) {
                        const n = dvss.findIndex( d => d.ID == e.dvs );
                        if ( n < 0 ){
                            e.dvsort = 9999999;
                            e.dvsname = null;
                        } else {
                            e.dvsort = dvss[n].sort;
                            e.dvsname= dvss[n].UF_NAME;
                            if ( /^1+/.test(dvss[n].UF_CODE) ){
                                e.class = 'head';
                            }
                        }
                    } else {
                        e.dvsort = 9999999;
                        e.dvsname = null;
                    }
                    
                    return e;
                }).filter( e => {
                    return (!dvs)||(dvs.ID < 1)||(e.red)||(e.dvs==dvs.ID);
                });
                
                const _sorting = (e1, e2) => {
                        if (e1.red) {
                            if (e2.red){
                                return e1.name.localeCompare(e2.name);
                            }
                            return -1;
                        }
                       
                        return e1.adt.isSame(e2.adt) 
                                    ? e1.dvsort < e2.dvsort ? -1 : 1
                                    : e1.adt.isBefore(e2.adt) ? -1 : 1;
                };  //_sorting
                
                if (!!at){
                    var at = $moment(at).format('YYYYMMDD');
                    return events.filter( e => at===e.adt.format('YYYYMMDD')).sort( _sorting );
                }
                return events.sort( _sorting );
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
            console.log('edit', event);
            const n = this.all.findIndex( a => a.ID === event.id );
            var item = n < 0 ? null : this.all[n];
            this.dialog = true;
            this.$nextTick(()=>{
                this.$refs[ (1==item.UF_RED) ? "dlgRed" : "dlgAct" ].open(item);
            });
        },
        async del({ event }){
            this.selected = [event];
            if ( !confirm('Подтвердите удаление для "' + event.name + '"') ){
                return;
            }
            try {
                if (event.red){
                    await this.$store.dispatch("data/rm", {reds: {ID: event.id}});
                } else {
                    await this.$store.dispatch("data/rm", {acts: {ID: event.id}});
                }
                this._fetch();
            } catch(e){
                console.log('ERR (del)', e);
                $nuxt.msg({type:'warning', text: `ОШИБКА удаления: ${e?.message || 'неизвестная'}`});
            }
        },  //del
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
        _to_top(){
            $(this.$el).find(".wp-calendar-conte").animate({ scrollTop: 0 }, "fast");
        },
        viewDay({ date }){
            this.value = date;
            this.type = 'day';
            this.$nextTick(this._to_top);
        },
        sel(event){
            console.log('sel', event);
            this.edit( { event } );
        },
        prev(){
            if (this.$refs.calendar){
                this.$refs.calendar.prev();
            } else {
                const d = this.$store.state.period.work.clone();
                this.$store.commit("set", {period: {work: d.add(-1, 'day')}});
                this.$nextTick(this._to_top);
            }
        },
        next(){
            if (this.$refs.calendar){
                this.$refs.calendar.next();
            } else {
                const d = this.$store.state.period.work.clone();
                this.$store.commit("set", {period: {work: d.add(1, 'day')}});
                this.$nextTick(this._to_top);
            }
        }
    },
    watch: {
        imp(val){
            if (!!val){
                this.doimp();
            }
        }
    }
}
</script>
<style lang="scss">
.v-calendar{
    height: 100%;
    &.v-calendar-weekly{
        min-height: 1900px !important;
    }
}

.v-event, .v-event-timed {
    & > *{
        padding-left: 0.33rem;
    }
    & .action{
        background: #fff;
        color: var(--v-primary-base);
    }
    & .red-day{
        background: #fff;
        color: $red-color;
    }
    & .allday{
        color: #0D47A1;
        background: #fff;
    }
    & .head{
        background: var(--v-primary-base);
        color: #fff;
    }
    & .not-complete, .not-ready{
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

.v-data-table.events-table{
    & tr > td:first-child{
        word-wrap: nowrap;
        white-space: nowrap;
    }
    & td{ 
        user-select: none;
        -moz-user-select: none;
        -webkit-user-select: none;
    }
    & .red-day {
        color: $red-color;
    }
    & .division{
        font-size: 0.75rem;
        color: var(--v-primary-lighten3);
        line-height: 1.115;
        margin: 0.25rem 0;
    }
}   /* v-data-table */

.wp-calendar-conte{
    overflow-y: auto;
    padding: 0 1rem 8rem 1rem;
}
</style>