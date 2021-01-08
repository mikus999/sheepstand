<template>
<v-bottom-navigation dark grow fixed class="light-blue darken-4 white--text" app>
  <v-btn router :to="{ name: 'home' }" class="text-decoration-none">
    <span>{{ $t('menu.dashboard') }}</span>
    <v-icon>{{ mdiViewDashboard }}</v-icon>
  </v-btn>

  <v-btn router :to="{ name: 'schedules.shifts' }" class="text-decoration-none" v-if="hasTeam">
    <span>{{ $t('menu.shifts') }}</span>
    <v-icon>{{ mdiCalendarMultiselect }}</v-icon>
  </v-btn>

  <v-btn router :to="{ name: 'account.inbox' }" class="text-decoration-none">
    <span>{{ $t('menu.inbox') }}</span>
    <v-badge :content="message_count.unread" color="red" overlap :value="message_count.unread">
      <v-icon>{{ mdiMessage }}</v-icon>
    </v-badge>
  </v-btn>

  <v-btn router :to="{ name: 'account.index' }" class="text-decoration-none">
    <span>{{ $t('menu.account') }}</span>
    <v-icon>{{ mdiAccountTie }}</v-icon>
  </v-btn>
</v-bottom-navigation>
</template>

<script>
import axios from 'axios'
import helper from '../mixins/helper'

export default {
  mixins: [helper],

}
</script>
