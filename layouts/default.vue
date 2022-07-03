<template>
  <v-app>
    <v-main>
      <wp-bar v-if="has('user')" 
              v-on:action="$refs['dlgAction'].open(-1)" />
      <v-container>
        <Nuxt />
      </v-container>
      <wp-dlg-action ref="dlgAction" />
    </v-main>
  </v-app>
</template>

<script>
import WpBar from '~/components/WpBar.vue';
import WpDlgAction from '~/components/WpDlgAction.vue';


export default {
  name: 'DefaultLayout',
  components: {
    WpBar,
    WpDlgAction
  },
  async asyncData({store}){
      return {
        user: await store.getters["user"]
      }
  },
  data(){
    return {
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
