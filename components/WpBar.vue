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
      <v-spacer />       
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
    </v-app-bar>
</template>
<script>

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
        }
    }
};
</script>
