<template>
  <v-navigation-drawer v-model="drawer" class="light-blue darken-4 white--text" dark style="z-index: 500;" app>
    <v-list flat class="mb-0 pb-0" v-show="!isMobile">
      <v-list-item>
        <v-list-item-content>
          <v-list-item-title>
            <Logo width="35" height="35" class="mb-2 mr-1"/>

            <span class="head sheep">SHEEP</span>
            <span class="head stand ml-n1">STAND</span>
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
      <v-list-group prepend-icon="mdi-calendar" v-if="hasTeam && $can(['view_schedules','manage_schedules'])" no-action active-class="menu-selected-item">
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
      <v-list-group prepend-icon="mdi-translate" v-if="$can('manage_translation')" no-action active-class="menu-selected-item">
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
        <TeamSelector v-if="!$vuetify.breakpoint.mobile && hasTeam" />
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
import Logo from './Logo'

export default {
  mixins: [helper],

  components: {
    TeamSelector,
    LocaleSelector,
    Logo
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

  .v-input {
    font-size: 10pt;
  }
</style>