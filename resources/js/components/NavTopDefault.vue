<template>
  <v-app-bar dark fixed dense flat :class="user ? 'light-blue darken-4 white--text' : 'grey darken-4 white--text'" app>
    <v-spacer v-if="isMobile" />

    <v-toolbar-title class="mb-0 pa-0">
      <Logo width="30" height="30" class="mb-n2 mr-1"/>
      <span class="head sheep small">SHEEP<span class="head stand">STAND</span></span>
    </v-toolbar-title>

    <v-spacer />

    <v-btn dark icon @click.prevent="logout" v-if="user">
      <v-icon>{{ icons.mdiLogoutVariant }}</v-icon>
    </v-btn>

  </v-app-bar>
</template>

<script>
import helper from '../mixins/helper'
import LocaleSelector from './LocaleSelector'
import Logo from './Logo'

export default {  
  mixins: [helper],

  components: {
    LocaleSelector,
    Logo
  },



  computed: {

    mini () {
      return this.$vuetify.breakpoint.mdAndDown;
    },

    isMobile () {
      return this.$vuetify.breakpoint.mobile
    },

  },
}
</script>