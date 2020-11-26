<template>
  <v-card width="100%">
    <v-data-table :headers="translatorHeaders" :items="translators" no-data-text="No Data" width="100%">
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>mdi-account-multiple</v-icon>
            Translators
          </v-toolbar-title>
        </v-toolbar>
      </template>

      <template v-slot:item.lang="{ item }">
        <p v-for="lang in item.translator_languages">{{ lang }}</p>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-btn icon>
          <v-icon>mdi-account-edit</v-icon>
        </v-btn>
      </template>
    </v-data-table>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'TranslatorManagement',
  mixins: [helper],

  data () {
    return {
      translators: [],
      translatorHeaders: [
        { text: 'Name', value: 'name', align: 'left' },
        { text: 'Email', value: 'email', align: 'left' },
        { text: 'Languages', value: 'lang', align: 'left' },
        { text: 'Actions', value: 'actions' },
      ],
    }
  },

  created () {
    this.getUserData()
  },

  methods: {
    async getUserData() {
      await axios.get('/api/users/translator')
        .then(response => {
          this.translators = response.data.users
        })
    },
  },
}
</script>