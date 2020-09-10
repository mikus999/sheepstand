<template>
  <v-bottom-navigation dark grow fixed hide-on-scroll class="light-blue darken-4 white--text" :horizontal="$vuetify.breakpoint.smAndUp">
    <v-btn router :to="{ name: 'home' }" class="text-decoration-none">
      <span>{{ $t('menu.dashboard') }}</span>
      <v-icon>mdi-view-dashboard</v-icon>
    </v-btn>

    <v-btn router :to="{ name: 'schedules.index' }" class="text-decoration-none">
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
import axios from 'axios'

export default {
  
}
</script>