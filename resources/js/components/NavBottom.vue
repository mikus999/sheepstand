<template>
  <v-bottom-navigation dark grow fixed class="light-blue darken-4 white--text" :horizontal="$vuetify.breakpoint.smAndUp" app>
    <v-btn router :to="{ name: 'home' }" class="text-decoration-none">
      <span>{{ $t('menu.dashboard') }}</span>
      <v-icon>mdi-view-dashboard</v-icon>
    </v-btn>

    <v-btn router :to="{ name: 'schedules.index' }" class="text-decoration-none" v-if="hasTeam">
      <span>{{ $t('menu.scheduling') }}</span>
      <v-icon>mdi-calendar</v-icon>
    </v-btn>
    
    <v-btn router :to="{ name: 'account.index' }" class="text-decoration-none">
      <span>{{ $t('menu.account') }}</span>
      <v-icon>mdi-account-tie</v-icon>
    </v-btn>    
  </v-bottom-navigation>
</template>


<script>
import { mapGetters, mapState } from 'vuex'
import axios from 'axios'

export default {
    computed: {
      ...mapGetters({
        user: 'auth/user',
        hasTeam: 'teams/hasTeam',
      }),
    }
}
</script>