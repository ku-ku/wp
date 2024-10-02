<template>
<v-container>
  <v-list class="wp">
    <v-list-item v-for="d in dates"
                 class="mt-3"
                 v-bind:class="{'current': now.isSame(d, 'day')}"
                 :data-item-at="d.toISOString()"
                 :key="'dt-' + d.toISOString()">
        <v-list-item-content>
            <v-col cols="auto" class="wp-dt" v-html="get('dt', d)"></v-col>
            <v-col>
              <ul class="red-days" v-if="get('reds', d).length > 0">
                <li v-for="r in get('reds', d)"
                    :key="'red-' + r.ID">
                    {{r.UF_TEXT}}
                </li>
              </ul>
              <v-row v-for="a in get('acts', d)"
                    :key="'act-' + a.ID">
                <v-col cols="12" class="wp-action">
                  {{a.UF_TEXT}}
                  <div class="wp-action__meta">
                    <span class="time" v-if="'00:00'!==a.at.format('HH:mm')">
                      <v-icon small>mdi-clock-outline</v-icon>
                      {{a.at.format('HH:mm')}}
                    </span>
                    <span class="loca" v-if="!empty(a.UF_PLACE)">
                      <v-icon small>mdi-map-marker-outline</v-icon>
                      {{a.UF_PLACE}}
                    </span>
                  </div>
                </v-col>
              </v-row>
            </v-col>
        </v-list-item-content>    
    </v-list-item>
  </v-list>
</v-container>
</template>

<script>
import { empty, gen_days } from "~/utils/"
import $moment from "moment";

$moment.locale('ru');


export default {
  name: 'WpIndexPage',
  async asyncData({ store }) {
        try {
            store.commit("default");
            store.commit("data/set", {acts: null, reds: null}); //clear before
            await store.dispatch("data/list", "acts");
            await store.dispatch("data/list", "reds");
        } catch(e){
            console.log('ERR (calendar)', e);
            $nuxt.msg({text: 'Ошибка получения списка мероприятий'});
        }
        
        return {
          now: $moment(),
          dates: gen_days()
        };
  }, 
  mounted(){
    this.$nextTick(()=>{
      const el = $(".v-list-item.current").get(0);
      if (!!el){
        this.$vuetify.goTo(el);
      }
    })
  },
  computed: {
      all(){
        return this.$store.getters["data/all"]?.filter( a => (1==a.UF_WWWATTR) );
      }
  },
  methods:{
    empty,
    get(q, v){
      switch(q){
        case "dt":
          return `<div class="day">${ v.format('DD') }</div><div class="month">${ v.format("MMMM")}</div><div class="week">${v.format("dddd")}</div>`;
        case "reds":
          return this.all?.filter( a => (1==a.UF_RED) && v.isSame(a.UF_ADT, 'day') );
        case "acts":
          return this.all?.filter( a => (0==a.UF_RED) && v.isSame(a.UF_ADT, 'day') ).map( a => {
            a.at = $moment(a.UF_ADT);
            return a;
          }).sort( (a1, a2) => {
            return a1.at.isBefore(a2.at)
                    ? -1
                    : a1.at.isAfter(a2.at) ? 1 : 0
          });
      }
    },  //get
  }
}
</script>
<style lang="scss">
.wp {
    & .row{
      & .col{
        padding-top: 0;
        padding-bottom: 0;
      }
    }
  &-dt{
    width: 8rem;
    line-height: 1.115;
    & .day{
      font-size: 3rem;
      color: var(--v-primary-base);
    }
    & .month{
      color: var(--v-secondary-base);
    }
    & .week{
      color: #4a65ce;
    }
  }
  & .current{
      & .day{
          border-radius: 500px;
          background: var(--v-primary-lighten3);
          color: #fff;
          width: 4rem;
          height: 4rem;
          line-height: 4rem;
          font-size: 2.5rem;
          text-align: center;
      }
  }
  & .red-days{
    font-size: 0.85rem;
    color: #d50000;
    margin-bottom: 1rem;
  }
  &-action{
    &__meta{
      font-size: 0.9rem;
      color:#9195a0;
      & .v-icon {
        margin-right: 0.35rem;
        color:#9195a0;
      }
    }
  }
}
</style>