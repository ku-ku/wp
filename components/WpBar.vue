<template>
    <v-app-bar app
               dark
               color="primary"
               fixed>
      <v-app-bar-nav-icon v-on:click.stop="$emit('navi')"></v-app-bar-nav-icon>
      <v-autocomplete dense
                      v-model="division"
                      :items="divisions"
                      menu-props="auto"
                      hide-details
                      no-data-text="нет данных"
                      hide-no-data
                      item-value="ID"
                      style="max-width:20rem;"
                      single-line
                      return-object>
                <template v-slot:selection="{ item }">
                    <div class="text-truncate">
                        {{item.UF_CODE}}. {{item.UF_NAME}}
                    </div>    
                </template>
                <template v-slot:item="{ item }">
                    <div class="text-truncate">
                        {{item.UF_CODE}}. {{item.UF_NAME}}
                    </div>    
                </template>
      </v-autocomplete>
      <template v-if="has('calendar')">
        <v-tooltip bottom>
            <template v-slot:activator="{ on, attrs }">
                <v-btn class="ma-2"
                       plain
                       v-bind="attrs"
                       @on="on"
                       v-on:click="type='month'">
                    <v-icon>mdi-calendar-month</v-icon>
                </v-btn>
            </template>
            <span>Календарь</span>
        </v-tooltip>
        <v-btn icon
            class="mr-2"
            v-on:click.stop.prevent="_emit('calendar', {type:'prev'})">
            <v-icon>mdi-chevron-left</v-icon>
        </v-btn>
        <v-btn outlined 
               style="width:8rem;"
               class="today"
               v-on:click="_emit('calendar', {type:'today'})">
            {{ get('today') }}
            <div class="text-muted">сегодня</div>
        </v-btn>
        <v-select v-model="type"
                  :items="[{name: 'Месяц', id: 'month'}, {name: 'День', id: 'day'}]"
                  item-text="name"
                  item-value="id"
                  dense
                  outlined
                  hide-details
                  class="mx-2"
                  label="Вид"
                  style="max-width:14rem;">
            <template v-slot:selection="{ item }">
                {{ item.id === 'day' ? item.name + ': ' + get('day') : item.name + ': ' + get('month') }}
            </template>
        </v-select>
        <v-btn icon
               v-on:click="_emit('calendar', {type:'refresh'})">
            <v-icon>mdi-refresh</v-icon>
        </v-btn>
        <v-btn
            icon
            class="ma-2"
            v-on:click.stop.prevent="_emit('calendar', {type:'next'})">
            <v-icon>mdi-chevron-right</v-icon>
        </v-btn>        
      </template>
      <template v-else>
        <v-btn text
                tile
                small
                v-on:click="$emit('action')">
          <v-icon small>mdi-bank-plus</v-icon>&nbsp;
          Добавить мероприятие
        </v-btn>
        <v-btn text
                tile
                small
                v-on:click="$emit('redday')">
          <v-icon small>mdi-calendar-plus</v-icon>&nbsp;
          Добавить праздничный день
        </v-btn>
      </template>  
      <v-spacer />       
      <v-btn text 
             tile
             v-on:click="$emit('report')">
          <v-icon small>mdi-file-word-outline</v-icon>
      </v-btn>    
    </v-app-bar>
</template>
<script>
import $moment from "moment";
$moment.locale('ru');
    
export default {
    name: "WpBar",
    computed: {
        divisions(){
            return [
                        {ID: -1, UF_CODE: '', UF_NAME:"ОБЩИЙ"},
                        ...this.$store.getters["data/divisions"].filter( d => (d.UF_ACTIVE == 1) )
            ];
        },  //divisions
        division: {
            get(){
                return this.$store.state.data.division;
            },
            set(val){
                this.$store.commit("data/set", {division: val});
            }
        },
        period(){
            return this.$store.state.period;
        },
        type: {
            get(){
                return this.$store.state.period.type || 'month';
            },
            set(val){
                this.$store.commit("set", {period: {type:val}});
            }
        }
    },
    methods: {
        has(q){
            switch(q){
                case 'calendar':
                    return 'calendar'===this.$route.name;
            }
            return false;
        },
        get(q){
            switch(q){
                case 'day':
                    return $moment(this.period.work).format('DD.MM.YYYY');
                case 'month':
                    return $moment(this.period.start).format('MMMM');
                case 'today':
                    return $moment().format('DD.MM.YYYY');
            }
            return '';
        },
        _emit(q, val){
            this.$eventHub.$emit(q, val);
            return false;
        }
    }
};
</script>
<style lang="scss">
    .today {
        &.v-btn {
            & .v-btn__content {
                font-size: 0.85rem;
                flex-direction: column;
                line-height: 1.125;
                & .text-muted {
                    font-size: 0.55rem;
                }
            }
        }            
    }
</style>