<template>
  <v-card outlined hover :width="width">
    <v-card-title class="justify-center text-h6">
      <v-icon class="mr-3">mdi-translate</v-icon>
      Translator Languages
    </v-card-title>

    <v-card-subtitle class="text-center font-weight-bold pt-4">
      {{ this.data.name }}
    </v-card-subtitle>

    <v-card-text class="my-5 overflow-auto" style="height: 300px;">
      <div>Available Languages</div>

      <v-divider class="mt-2 mb-n2"/>

      <v-checkbox 
        v-for="lang in languages" 
        v-model="selected_languages"
        :key="lang.id" 
        :label="lang.name" 
        :value="lang.id" 
        dense
        hide-details
      />

    </v-card-text>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn text v-on:click="$emit('close')">
        {{ $t('general.cancel' ) }}
      </v-btn>
      <v-btn color="primary" text v-on:click="saveLanguages()">
        {{ $t('general.save' ) }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'TranslatorLanguages',
  mixins: [helper],
  props: {
    data: {
      type: [Object, Array]
    },
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
        url: '/api/translation/languages/site',
      })
      .then(response => {
        this.languages = response.data
      })

      this.data.languages.forEach((language, index) => {
        this.selected_languages.push(language.id)
      })
    },

    async saveLanguages() {
      await axios({
        method: 'post',      
        url: '/api/translation/permissions',
        data: {
          user_id: this.data.id,
          languages: this.selected_languages
        }
      })
      .then(response => {
        this.showSnackbar('Translator permissions updated successfully', 'success')
        this.$emit('close')
      })
      .catch(error => {
        this.showSnackbar('Error: ' + error, 'error')
      })

    }
  },
}
</script>