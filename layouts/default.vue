<template>
  <v-app>
    <v-main>
      <wp-bar v-if="has('user')" 
              v-on:navi="navi = !navi"
              v-on:action="add('dlgAct')"
              v-on:redday="add('dlgRed')"
              v-on:report="doreport">
      </wp-bar>
      <v-navigation-drawer v-model="navi" 
                           dark
                           color="primary"
                           temporary>
        <v-list v-if="has('user')">
          <v-list-item v-bind:class="{'wp-admin': has('admin')}">
            <v-list-item-icon>
              <v-icon>mdi-account</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title class="text-h6">
                {{user.name}}
              </v-list-item-title>
              <v-list-item-subtitle>({{user.login}})</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
        </v-list>
        <v-divider></v-divider>        
        <v-list nav dense>
          <v-list-item to="/">
            <v-list-item-icon><v-icon>mdi-calendar-month</v-icon></v-list-item-icon>
            <v-list-item-title>План мероприятий</v-list-item-title>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-item :to="{path:'/calendar', replace: true}">
            <v-list-item-icon><v-icon>mdi-calendar-text</v-icon></v-list-item-icon>
            <v-list-item-title>Календарь</v-list-item-title>
          </v-list-item>
          <v-list-item :to="{path: '/info/divisions', replace: true}">
            <v-list-item-icon><v-icon>mdi-office-building</v-icon></v-list-item-icon>
            <v-list-item-title>Структура</v-list-item-title>
          </v-list-item>
          <v-list-item :to="{path: '/info/staffing', replace: true}">
            <v-list-item-icon><v-icon>mdi-card-account-details-outline</v-icon></v-list-item-icon>
            <v-list-item-title>Должности</v-list-item-title>
          </v-list-item>
          <v-list-item :to="{path: '/info/users', replace: true}">
            <v-list-item-icon><v-icon>mdi-account-supervisor</v-icon></v-list-item-icon>
            <v-list-item-title>Пользователи</v-list-item-title>
          </v-list-item>
          <v-list-item :to="{path: '/info/employees', replace: true}">
            <v-list-item-icon><v-icon>mdi-account-tie</v-icon></v-list-item-icon>
            <v-list-item-title>Сотрудники</v-list-item-title>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-item v-on:click="movereds()">
            <v-list-item-icon><v-icon>mdi-calendar-arrow-right</v-icon></v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Перенести праздники...</v-list-item-title>
            </v-list-item-content>
          </v-list-item>
          <v-divider></v-divider>
          <v-list-item v-on:click="doimp()">
            <v-list-item-icon><v-icon>mdi-table-arrow-up</v-icon></v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Импортировать...</v-list-item-title>
              <v-list-item-subtitle>{{impPeriod}}</v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
          <v-divider></v-divider>
        </v-list>
      </v-navigation-drawer>  
      <v-container fluid>
        <Nuxt />
      </v-container>      
    </v-main>
    <wp-dialog v-if="dialog"
               ref="dlgAct" :mode="DIA_MODES.action" />
    <wp-dialog v-if="dialog"
               ref="dlgRed" :mode="DIA_MODES.reday" />
    <wp-dialog ref="dlgMoveReds" 
               :mode="DIA_MODES.movereds" />
    <wp-dates ref="dates" />
  </v-app>
</template>

<script>
import Vue from 'vue';
import { mapState } from 'vuex';
import $moment from "moment";
$moment.locale("ru");

import WpBar from '~/components/WpBar.vue';
import WpDialog from '~/components/WpDialog.vue';
import WpAuthBtn from '~/components/WpAuthBtn.vue';
import WpDates from '~/components/WpDates.vue';

import { DIA_MODES, gen_days, empty, shorting } from '~/utils';


export default {
  name: 'DefaultLayout',
  components: {
    WpBar,
    WpDialog
  },
  data(){
    return {
      DIA_MODES,
      navi: null,
      dialog: false
    }
  },
  computed:  {
    ...mapState({
          user: state => state.data.user,
          impPeriod: state => {
            return state.period.start?.format("MMM, YYYY");
          }
    })
  },
  methods: {
    has(q){
      switch(q){
        case "admin":
          return (!!this.user?.adm);
        case "user":
          return (this.user?.id > 0);
      }
    },
    add(q){
      this.dialog = true;
      this.$nextTick(()=>{
        this.$refs[q].open({ID:-1});
      });
    },
    doimp(){
      this.$router.replace({path: '/calendar', query: {imp: (new Date()).getTime()}});
    },
    async doreport(){
        const p = await this.$refs["dates"].open();
        if (!p){
            return;
        }
        
        try {
            
            const days = gen_days(p.start, p.end);
            p.start = $moment(p.start);
            p.end   = $moment(p.end);        //.add(1, 'days').add(-1, 'seconds');
            
            
            const opts = {
                    url: $nuxt.context.env.apiUrl,
                    data: {
                        q: "acts",
                        params: {
                                    fully: 1,
                                    period: {
                                        start: p.start.toISOString(),
                                        end: p.end.toISOString()
                                    }
                        }
                    },
                    dataType: "json"
            };
            
            const all = await $.ajax(opts);
            
            opts.data.q = "reds";
            const reds= await $.ajax(opts);
            const dvss= this.$store.getters["data/divisions"]||[];
            const stfs= this.$store.getters["data/staffing"]||[];
            
            const data = {
                start: p.start.format('DD.MM.YYYY'),
                end:   p.end.format('DD.MM.YYYY'),
                days:  days.map( d => {
                    const day = {
                        day: {
                            date: d.toISOString(),
                            day: d.format('DD dddd')
                        },
                        reds: reds.filter(a => (1==a.UF_RED && d.isSame(a.UF_ADT, 'day') )).map( a=> {
                            return {
                                id: a.ID,
                                name: a.UF_TEXT
                            };
                        }),
                        acts: all.filter(a => ( 1!=a.UF_RED && 1==a.UF_READY && d.isSame(a.UF_ADT, 'day') ))
                                  .map( a => {
                                      a.at = $moment(a.UF_ADT);
                                      var n = dvss.findIndex( dvs => dvs.ID == a.UF_DVS);
                                      a.dsort = (n < 0) ? 9999999 : dvss[n].sort;               //sorting by division
                                      n = stfs.findIndex( stf => stf.ID == a.CHIEF?.UF_STAFF);
                                      a.ssort = (n < 0) ? 9999999 : Number(stfs[n].UF_SORT);    //staff sorting
                                      
                                      const hdrs = [shorting(a.CHIEF_NAME)];
                                      a.HEADNAMES.forEach( name => {
                                          var name = shorting(name);
                                          if ( hdrs.findIndex(e => e==name) < 0 ){
                                            hdrs.push(name);
                                          }
                                      });
                                      a.hdrs = hdrs.join(',\n');
                                      
                                      const emps = [];
                                      a.EMPNAMES.forEach( name => {
                                          var name = shorting(name);
                                          if ( emps.findIndex(e => e==name) < 0 ){
                                            emps.push(name);
                                          }
                                      });
                                      a.emps = emps.join(',\n');
                                      
                                      a.sort = (a.UF_DAYATTR == 1) 
                                                    ? Number.MAX_VALUE
                                                    : (a.at.hours() * 100 + a.at.minutes()) * 10000000 + a.dsort;
                                      
                                      return a;
                                  })
                                  .sort( (d1, d2) => {
                                        return (d1.sort < d2.sort)
                                                    ? -1
                                                    : (d1.sort > d2.sort) ? 1 : 0;

                                  }).map( a => {
                                      return {
                                          id: a.ID,
                                          tm: (1==a.UF_DAYATTR) ? ' ' : a.at.format('HH:mm').replace('00:00', ' '),
                                          name: a.UF_TEXT,
                                          place: a.UF_PLACE,
                                          chief: a.hdrs,
                                          emps:  a.emps,
                                          sort:  `${a.dsort} - ${a.ssort}`,
                                          day:   (1==a.UF_DAYATTR)
                                      };
                                  })
                    };
                    return day;
                })
            };

            console.log('report', data);
            $.ajax({
                    url: `${ $nuxt.context.env.apiUrl }/exp-doc.php`,
                    type: "POST",
                    contentType: 'application/json',
                    processData: false,
                    data: JSON.stringify(data),
                    xhrFields: {
                        responseType: 'blob' 
                    },
                    cache: false,
                    success: resp => {
                        const blob = new Blob([resp], { type: "application/vnd.openxmlformats-officedocument.wordprocessingml.document"});
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download=`report-${ p.start.format('MM-YYYY') }.docx`;
                        link.click();
                        //window.location.href = downloadUrl;
                    }
            }).catch( e => {
                console.log('ERR (report)', e);
            });
        } catch(e){
            console.log('ERR (report)', e);
            $nuxt.msg({text:"Ошибка формирования отчета, попробуйте еще раз", color: "warning"});
        }
    },   //doreport
    movereds(){
        this.$refs["dlgMoveReds"].open();
    }
  }
}
</script>
<style lang="scss">
.v-navigation-drawer{
  position: fixed;
  top: 64px !important;
  & .wp-admin{
    & .v-icon{color: $red-color;}
  }
}
</style>