<template>
    <v-app-bar app
               dark
               color="primary">
          <v-spacer />       
          <v-btn text
                 tile
                 small
                 v-on:click="$emit('action')">
            <v-icon small>mdi-bank-plus</v-icon>
            Добавить мероприятие
          </v-btn>
          <v-btn text
                 tile
                 small
                 v-on:click="$emit('holiday')">
            <v-icon small>mdi-calendar-plus</v-icon>
            Добавить праздничный день
          </v-btn>
    </v-app-bar>
</template>
<script>

export default {
    name: "WpBar",
};
</script>
<style lang="scss" scoped>
  .v-toolbar{
    &__content{
      & .v-icon{
        margin-right: 0.5rem;
      }
    }
  }
</style>