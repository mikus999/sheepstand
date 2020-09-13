<template>
  <v-navigation-drawer v-model="drawer" class="light-blue darken-4 white--text" dark style="z-index: 500;" app>
    <v-list flat class="mb-0 pb-0" v-show="!isMobile">
      <v-list-item>
        <v-list-item-content>
          <v-list-item-title class="title">
            <svg width="25" height="25" version="1.1" fill="currentColor" viewBox="0 0 12.7 12.7" xmlns="http://www.w3.org/2000/svg">
            <g transform="scale(.98535 1.0149)" aria-label="C">
              <path d="m11.445 9.3097q-1.6102 2.0742-4.4213 2.0742-2.3744 0-3.8891-1.501-1.5147-1.501-1.5147-3.7936 0-1.4192 0.69594-2.62 0.70959-1.2008 1.9514-1.8831 1.2418-0.68229 2.6746-0.68229 1.4465 0 2.5791 0.55948 1.1463 0.54584 1.9104 1.6102l-0.90063 0.69594q-1.4192-1.7194-3.507-1.7194-1.7057 0-2.9475 1.2008-1.2281 1.2008-1.2281 2.9475 0 1.733 1.2008 2.9066 1.2145 1.1599 3.1386 1.1599t3.3432-1.6512z"/>
            </g>
            <path transform="scale(.26458)" d="m22.486 10.08v15.014h3.2988v-0.009766h12.992v-3.2656h-12.992v-11.738h-3.2988z"/>
            </svg>

            <span class="head-thick">CART</span>
            <span class="head-thin ml-n1">PLAN</span>
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>

    <v-list flat v-if="user" :dense="$vuetify.breakpoint.xs">
      <v-divider class="ma-1" v-show="!isMobile"/>

      <v-list-item router :to="{ name: 'home' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>mdi-view-dashboard</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.dashboard') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <!-- TEAMS -->
      <v-list-group prepend-icon="mdi-account-group" no-action active-class="menu-selected-item">
        <template v-slot:activator>
          <v-list-item-content>
            <v-list-item-title>{{ $t('menu.team') }}</v-list-item-title>
          </v-list-item-content>
        </template>


        <v-list-item router :to="{ name: 'teams.index' }" class="text-decoration-none" v-if="hasTeam">
          <v-icon class="menu-subitem-icon">mdi-cog</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.team_settings') }}</v-list-item-title>
        </v-list-item>

        <v-list-item router :to="{ name: 'teams.join' }" class="text-decoration-none">
          <v-icon class="menu-subitem-icon">mdi-account-multiple-plus</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.join_another_team') }}</v-list-item-title>
        </v-list-item>

      </v-list-group>

      <!-- SCHEDULING -->
      <v-list-group prepend-icon="mdi-calendar" v-if="hasTeam" no-action active-class="menu-selected-item">
        <template v-slot:activator>
          <v-list-item-content>
            <v-list-item-title>
              {{ $t('menu.scheduling') }}
            </v-list-item-title>
          </v-list-item-content>
        </template>

        <v-list-item disabled class="text-decoration-none">
          <v-icon class="menu-subitem-icon">mdi-account-details</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.assignments') }}</v-list-item-title>
        </v-list-item>

        <v-list-item router :to="{ name: 'schedules.index' }" class="text-decoration-none">
          <v-icon class="menu-subitem-icon">mdi-calendar-clock</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.shift_planning') }}</v-list-item-title>
        </v-list-item>

        <v-divider inset />
        <v-subheader inset>Settings</v-subheader>

        <v-list-item router :to="{ name: 'teams.locations' }" class="text-decoration-none" v-if="hasTeam">
          <v-icon class="menu-subitem-icon">mdi-map-marker-multiple</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.locations') }}</v-list-item-title>
        </v-list-item>
      </v-list-group>



      <!-- TRANSLATION -->
      <v-list-group prepend-icon="mdi-translate" v-if="isTranslator" no-action active-class="menu-selected-item">
        <template v-slot:activator>
          <v-list-item-content>
            <v-list-item-title>
              {{ $t('menu.translation') }}
            </v-list-item-title>
          </v-list-item-content>
        </template>

        <v-list-item router :to="{ name: 'translation.index' }" class="text-decoration-none">
          <v-icon class="menu-subitem-icon">mdi-tooltip-edit</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.translation_manager') }}</v-list-item-title>
        </v-list-item>
      </v-list-group>



      <!-- ACCOUNT -->
      <v-list-group prepend-icon="mdi-account-tie" no-action active-class="menu-selected-item">
        <template v-slot:activator>
          <v-list-item-content>
            <v-list-item-title>{{ $t('menu.account') }}</v-list-item-title>
          </v-list-item-content>
        </template>

        <v-list-item disabled class="text-decoration-none">
          <v-icon class="menu-subitem-icon">mdi-lock-reset</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.change_password') }}</v-list-item-title>
        </v-list-item>
        
        <v-list-item router :to="{ name: 'account.index' }" class="text-decoration-none">
          <v-icon class="menu-subitem-icon">mdi-account-cog</v-icon>
          <v-list-item-title class="menu-subitem-label">{{ $t('menu.account_settings') }}</v-list-item-title>
        </v-list-item>

        <v-list-item class="text-decoration-none">
          <v-list-item-content>
          <v-switch v-model="$vuetify.theme.dark" hide-details :label="$t('menu.dark_theme')" color="black" class="radio-sm"></v-switch>
          </v-list-item-content>
        </v-list-item>
      </v-list-group>

    </v-list>

    <template v-slot:append v-if="user">
      <!-- TEAM SELECTOR -->
      <div class="pa-1">
        <TeamSelector v-if="!$vuetify.breakpoint.mobile" />
      </div>


      <!-- LANGUAGE SELECTOR -->
      <div class="pa-1">
        <LocaleSelector v-if="!$vuetify.breakpoint.mobile" />
      </div>


      <!-- LOGOUT BUTTON -->
      <div class="pa-1">
        <v-btn block @click.prevent="logout" v-if="!$vuetify.breakpoint.mobile">
          <v-icon>mdi-logout-variant</v-icon>
          <span class="ml-3">{{ $t('auth.logout') }}</span>
        </v-btn>
      </div>
    </template>
  </v-navigation-drawer>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import axios from 'axios'
import Cookies from 'js-cookie'
import { loadMessages } from '~/plugins/i18n'
import helper from '../mixins/helper'
import TeamSelector from './TeamSelector'
import LocaleSelector from './LocaleSelector'


export default {
  mixins: [helper],

  components: {
    TeamSelector,
    LocaleSelector
  },

  data () {
    return {
      drawer: !this.$vuetify.breakpoint.mobile,
      isTranslator: true
    }
  },

  computed: {

    mini () {
      return this.$vuetify.breakpoint.mdAndDown;
    },

    isMobile () {
      return this.$vuetify.breakpoint.mobile
    },

  },

  toggleDarkMode () {
    this.$vuetify.theme.dark = !this.$vuetify.theme.dark
  }
}
</script>

<style scoped>
  .radio-sm .v-label {
    font-size: 11pt;
    color: #ffffff;
  }

  .menu-subitem-label {
    font-size: 11pt;
  }

  .menu-subitem-icon {
    padding-right: 15px;
  }

  .menu-selected-item {
    color: #ffffff;
  }

  .v-input {
    font-size: 10pt;
  }
</style>