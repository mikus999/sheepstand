<template>
  <v-app-bar dark fixed dense flat class="light-blue darken-4 white--text" app>
    <v-app-bar-nav-icon @click.stop="$emit('toggle-drawer')"></v-app-bar-nav-icon>

    <v-toolbar-title>
      <span class="head-thick">CART</span>
      <span class="head-thin ml-n1">PLAN</span>
    </v-toolbar-title>

    <v-spacer />

    <TeamSelector />
    <LocaleSelector />

    <v-btn dark icon @click.prevent="logout">
      <v-icon>mdi-logout-variant</v-icon>
    </v-btn>
  </v-app-bar>
</template>

<script>
import helper from '../mixins/helper'
import TeamSelector from './TeamSelector'
import LocaleSelector from './LocaleSelector'

export default {
  mixins: [helper],
  
  components: {
    TeamSelector,
    LocaleSelector
  },
}
</script>