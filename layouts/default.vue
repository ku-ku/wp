<template>
  <v-app>
    <v-main>
      <wp-bar v-if="has('user')" 
              v-on:navi="navi = !navi"
              v-on:action="$refs['dlgAct'].open()"
              v-on:redday="$refs['dlgRed'].open()">
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
    <wp-dialog ref="dlgAct" :mode="DIA_MODES.action" />
    <wp-dialog ref="dlgRed" :mode="DIA_MODES.reday" />
  </v-app>
</template>

<script>
import { mapState } from 'vuex';

import WpBar from '~/components/WpBar.vue';
import WpDialog from '~/components/WpDialog.vue';
import { DIA_MODES } from '~/utils';

export default {
  name: 'DefaultLayout',
  components: {
    WpBar,
    WpDialog
  },
  data(){
    return {
      DIA_MODES,
      navi: null
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
    doimp(){
      this.$router.replace({path: '/calendar', query: {imp: (new Date()).getTime()}});
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