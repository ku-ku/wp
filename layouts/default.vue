<template>
  <v-app>
    <v-main>
      <wp-bar v-if="has('user')" 
              v-on:navi="navi = !navi"
              v-on:action="$refs['dlg'].open(DIA_MODES.action, -1)"
              v-on:redday="$refs['dlg'].open(DIA_MODES.reday, -1)">
      </wp-bar>
      <v-navigation-drawer v-model="navi" 
                           dark
                           color="primary"
                           absolute temporary>
        <v-list dense>
          <v-list-item>
            <v-list-item-icon><v-icon>mdi-account-supervisor</v-icon></v-list-item-icon>
            <v-list-item-title>Пользователи</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-navigation-drawer>
      <v-container>
        <Nuxt />
      </v-container>
      <wp-dialog ref="dlg" />
    </v-main>
  </v-app>
</template>

<script>

import WpBar from '~/components/WpBar.vue';
import WpDialog from '~/components/WpDialog.vue';
import { DIA_MODES } from '~/utils';

export default {
  name: 'DefaultLayout',
  components: {
    WpBar,
    WpDialog
  },
  async asyncData({store}){
      return {
        user: await store.getters["user"]
      }
  },
  data(){
    return {
      DIA_MODES,
      navi: null
    }
  },
  methods: {
    has(q){
      switch(q){
        case "user":
          return true; //(this.user?.id > 0);
      }
    }
  }
}
</script>
