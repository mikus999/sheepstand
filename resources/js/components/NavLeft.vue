<template>
  <v-navigation-drawer v-model="drawer" clipped class="light-blue darken-4 white--text" dark app>

    <v-list v-if="user" class="pt-lg-0" dense>

      <v-list-item router :to="{ name: 'dashboard' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiViewDashboard }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.dashboard') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-list-item router :to="{ name: 'schedules.shifts' }" class="text-decoration-none" active-class="menu-selected-item" v-if="user && hasTeam">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiCalendarMultiselect }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.shifts') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-list-item router :to="{ name: 'teams.join' }" class="text-decoration-none" active-class="menu-selected-item" v-else>
        <v-list-item-icon>
          <v-icon>{{ icons.mdiAccountMultiplePlus }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('teams.join_team') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>


      <v-list-item router :to="{ name: 'account.inbox' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-badge :content="message_count.unread" color="red" overlap :value="message_count.unread">
            <v-icon>{{ icons.mdiMessage }}</v-icon>
          </v-badge>

        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>
              {{ $t('menu.inbox') }} 
          </v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-list-item router :to="{ name: 'account.index' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiAccountTie }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.account') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>




    <v-list dense>

      <v-list-item class="text-decoration-none" v-if="user && hasTeam">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiAccountGroup }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <TeamSelector v-if="user && hasTeam" />
        </v-list-item-content>
      </v-list-item>
      
      <v-list-item class="text-decoration-none">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiTranslate }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <LocaleSelector />
        </v-list-item-content>
      </v-list-item>
    </v-list>

    <v-list v-if="user && hasTeam && $can(['view_schedules','manage_schedules'])" dense>
      <v-subheader>{{ $t('menu.team_admin')}}</v-subheader>

      <v-list-item router :to="{ name: 'schedules.index' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiCalendarClock }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.scheduling') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-list-item router :to="{ name: 'teams.index', params: { tab: 'general' } }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiCog }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.team_settings') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-list-item router :to="{ name: 'teams.messages' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiMessageCog }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.messages') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>


    <v-list v-if="user && $is(['translator','super_admin'])" dense>
      <v-subheader>{{ $t('menu.site_admin')}}</v-subheader>

      <v-list-item v-if="$is('translator')" router :to="{ name: 'translation.index' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiGoogleTranslate }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.translation') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>

      <v-list-item v-if="$is('super_admin')" router :to="{ name: 'admin.index' }" class="text-decoration-none" active-class="menu-selected-item">
        <v-list-item-icon>
          <v-icon>{{ icons.mdiTools }}</v-icon>
        </v-list-item-icon>
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.site_admin') }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>


    <template v-slot:append>
      <div class="pa-1">
        <v-switch v-model="$vuetify.theme.dark" hide-details color="black"></v-switch>
      </div>

      <!-- LOGOUT BUTTON -->
      <div :class="'pa-1 ' + ($vuetify.breakpoint.xs ? 'mb-16' : '')">
        <v-btn block @click.prevent="logout" v-if="!$vuetify.breakpoint.mobile && user">
          <v-icon>{{ icons.mdiLogoutVariant }}</v-icon>
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
    font-size: 9pt;
    color: #ffffff;
  }

  .v-input {
    font-size: 10pt;
  }

  .v-list-item__title {
    font-size: 0.85rem !important;
  }


</style>
