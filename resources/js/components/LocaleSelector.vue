<template>

  <v-menu offset-y bottom>
    <template v-slot:activator="{ on, attrs }">
      <div width="100%" v-bind="attrs" v-on="on">
        <span class="menu-label d-inline-block text-truncate mb-n1" style="max-width: 150px;">{{ languageName }}</span>
        <v-icon class="float-right">mdi-menu-down</v-icon>
      </div>
    </template>

    <v-list dense>
      <v-list-item v-for="l in languages" :key="l.id" @click="setLocale(l.code)">
        <v-list-item-title>{{ l.native_name }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>


  <!-- IF MOBILE DEVICE, SHOW DROPDOWN MENU ON NAVBAR INSTEAD OF SELECT
  <v-menu v-else offset-y bottom left>
    <template v-slot:activator="{ on, attrs }">
      <v-btn dark icon v-bind="attrs" v-on="on">
        <v-icon>mdi-translate</v-icon>
      </v-btn>
    </template>

    <v-list>
      <v-list-item v-for="l in languages" :key="l.id" @click="setLocale(l.code)">
        <v-list-item-title>{{ l.native_name }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
  -->
</template>

<script>
import axios from 'axios'
import helper from '../mixins/helper'

export default {
  name: 'LocaleSelector',
  mixins: [helper],

  data() {
    return {
      languages: [],
    }
  },

  computed: {
    languageName() {
      var result = ''
      var tempArr = this.languages.filter(l => l.code == this.locale)
      if (tempArr.length > 0) {
        result = tempArr[0].native_name
      }
      return result
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
    },

  }

}
</script>

<style scoped>
.menu-label{
  font-size: 10pt;
}
</style>
