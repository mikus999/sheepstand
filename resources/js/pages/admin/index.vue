<template>
  <v-container fluid>
    <v-row>
      <PageTitle title="Site Administration"></PageTitle>
    </v-row>

    <v-row>
      <TranslatorManagement />
    </v-row>

  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import TranslatorManagement from '~/components/TranslatorManagement'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: { 
    TranslatorManagement
  },
}
</script>