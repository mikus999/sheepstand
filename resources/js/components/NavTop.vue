<template>
  <v-app-bar dark fixed dense flat clipped-left class="light-blue darken-4 white--text" app>

    <v-app-bar-nav-icon small @click.stop="$emit('toggle-drawer')" class="ml-sm-2" v-if="!hideSidebar && $vuetify.breakpoint.mdAndUp">
      <template v-slot:default>
        <v-icon v-if="!isMobile && drawer">{{ icons.mdiBackburger}}</v-icon>
        <v-icon v-else>{{ icons.mdiMenu}}</v-icon>
      </template>
    </v-app-bar-nav-icon>
    <v-btn small icon disabled class="ml-sm-2" v-else></v-btn>

    <v-spacer />

    <v-toolbar-title class="mb-0 pa-0">
      <router-link :to="{ name: 'default' }" class="text-decoration-none">
        <Logo width="30" height="30" class="mb-n2 mr-1"/>
        <span class="head sheep small">SHEEP<span class="head stand">STAND</span></span>
      </router-link>
    </v-toolbar-title>

    <v-spacer />

    <!--
    <v-btn dark icon @click.prevent="logout" v-if="user">
      <v-icon>{{ icons.mdiLogoutVariant }}</v-icon>
    </v-btn>
    -->

    <v-btn icon small :to="{ name: 'dashboard' }" v-if="hideSidebar">
      <v-icon>{{ icons.mdiViewDashboard}}</v-icon>
    </v-btn>

    <v-btn icon small :to="{ name: 'default' }" v-else>
      <v-icon>{{ icons.mdiHome}}</v-icon>
    </v-btn>

    <v-menu 
      offset-y 
      transition="scroll-y-transition" 
      bottom 
      right 
      :close-on-content-click="true"
      :close-on-click="true"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-avatar v-if="user" size="30" color="white" v-bind="attrs" v-on="on" class="ml-3">
          <v-img :src="user.photo_url" v-if="user.photo_url" />
          <v-icon v-else>{{ icons.mdiAccount }}</v-icon>
        </v-avatar>

        <v-btn icon small v-else v-bind="attrs" v-on="on" class="ml-3">
          <v-icon>{{ icons.mdiDotsVertical}}</v-icon>
        </v-btn>
      </template>

      <ProfileCard />
    </v-menu>

  </v-app-bar>
</template>

<script>
import helper from '../mixins/helper'
import TeamSelector from './TeamSelector'
import LocaleSelector from './LocaleSelector'
import Logo from './Logo'
import ProfileCard from './ProfileCard'

export default {
  mixins: [helper],
  props: {
    hideSidebar: {
      type: Boolean,
      default: false
    },
    drawer: {
      type: Boolean,
      default: false
    }
  },
  components: {
    TeamSelector,
    LocaleSelector,
    Logo,
    ProfileCard
  },


  computed: {

    mini () {
      return this.$vuetify.breakpoint.mdAndDown;
    },

    isMobile () {
      return this.$vuetify.breakpoint.smAndDown
    },

  },
}
</script>