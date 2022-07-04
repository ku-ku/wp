<template>
    <v-app-bar app
               dark
               color="primary">
      <v-app-bar-nav-icon v-on:click.stop="$emit('navi')"></v-app-bar-nav-icon>
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
};
</script>
<style lang="scss" scoped>
  .v-toolbar{
    &__content{
    }
  }
</style>