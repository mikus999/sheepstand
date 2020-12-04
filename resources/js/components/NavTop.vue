<template>
  <v-app-bar dark fixed dense flat class="light-blue darken-4 white--text" app>
    <v-app-bar-nav-icon @click.stop="$emit('toggle-drawer')"></v-app-bar-nav-icon>

    <v-toolbar-title>
      <span class="head sheep small">SHEEP<span class="head stand">STAND</span></span>
    </v-toolbar-title>

    <v-spacer />

    <TeamSelector v-if="user && hasTeam" />
    <v-btn v-else-if="user && !hasTeam" dark icon @click.prevent="$router.push({ name: 'teams.join' })">
      <v-icon>mdi-account-multiple-plus</v-icon>
    </v-btn>

    <v-btn dark icon @click.prevent="logout" v-if="user">
      <v-icon>mdi-logout-variant</v-icon>
    </v-btn>

    <LocaleSelector />
  </v-app-bar>
</template>

<script>
import helper from '../mixins/helper'
import TeamSelector from './TeamSelector'
import LocaleSelector from './LocaleSelector'
import Logo from './Logo'

export default {
  mixins: [helper],
  
  components: {
    TeamSelector,
    LocaleSelector,
    Logo
  },
}
</script>