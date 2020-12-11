<template>
  <v-card hover :width="width">
    <v-card-title class="justify-center text-h6">
      <v-icon class="mr-3">mdi-translate</v-icon>
      Site Languages
    </v-card-title>

    <v-card-text class="my-5 overflow-auto" style="height: 300px;">
      <div>Available Languages</div>

      <v-divider class="mt-2 mb-n2"/>

      <v-checkbox 
        v-for="lang in languages" 
        v-model="selected_languages"
        :key="lang.code" 
        :label="lang.name" 
        :value="lang.code" 
        :disabled="lang.code == 'en'"
        @change="changeLanguage(lang.code)"
        dense
        hide-details
      />

    </v-card-text>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="primary" text v-on:click="$emit('close')">
        {{ $t('general.close' ) }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'SiteLanguages',
  mixins: [helper],
  props: {
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '170px'
    },
  },
  
  data() {
    return {
      languages: [],
      selected_languages: []
    }
  },

  created() {
    this.getLanguages()
  },

  methods: {
    async getLanguages() {
      await axios({
        method: 'get',      
        url: '/api/translation/languages/all',
      })
      .then(response => {
        this.languages = response.data
      })

      this.languages.filter(l => l.site_language == true).forEach((language, index) => {
        this.selected_languages.push(language.code)
      })
    },

    async changeLanguage(lang) {

      if (this.selected_languages.indexOf(lang) >= 0) {
        var changetype = 'add'
      } else {
        var changetype = 'remove'
      }

      await axios({
        method: 'post',      
        url: '/api/translation/languages/edit/',
        data: {
          language: lang,
          changetype: changetype
        }
      })
      .then(response => {
        this.showSnackbar('Site languages updated successfully', 'success')
      })
      .catch(error => {
        this.showSnackbar('Error: ' + error, 'error')
      })

    }
  },
}
</script>