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
        <div v-for="lang in item.languages">{{ lang.name }}</div>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-btn icon @click="showLanguageOverly(item)">
          <v-icon>mdi-account-edit</v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <v-overlay :value="langOverlay" :dark="theme=='dark'">
      <TranslatorLanguages :data="currUser" v-on:close="closeLanguageOverlay()" width="300px" height="100%"></TranslatorLanguages>
    </v-overlay>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import TranslatorLanguages from '~/components/TranslatorLanguages.vue'

export default {
  name: 'TranslatorManagement',
  mixins: [helper],
  components: {
    TranslatorLanguages
  },

  data () {
    return {
      translators: [],
      translatorHeaders: [
        { text: 'Name', value: 'name', align: 'left' },
        { text: 'Email', value: 'email', align: 'left' },
        { text: 'Languages', value: 'lang', align: 'left' },
        { text: 'Actions', value: 'actions' },
      ],
      currUser: null,
      langOverlay: false
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

    showLanguageOverly(user) {
      this.currUser = user
      this.langOverlay = true
    },

    closeLanguageOverlay() {
      this.getUserData()
      this.currUser = null
      this.langOverlay = false
    }
  },
}
</script>