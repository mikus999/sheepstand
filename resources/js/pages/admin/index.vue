<template>
  <v-container fluid>
    <v-row class="mb-12">
      <UserTable :team-users="false" />
    </v-row>

    <v-row class="mb-12">
      <TranslatorManagement />
    </v-row>

  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import TranslatorManagement from '~/components/TranslatorManagement'
import UserTable from '~/components/UserTable'

export default {
  middleware: ['auth'],
  layout: 'sidebar',
  mixins: [helper],
  components: { 
    TranslatorManagement,
    UserTable
  },
}
</script>