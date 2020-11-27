<template>
  <v-select v-if="!$vuetify.breakpoint.mobile" :items="languages" item-text="native_name" item-value="code" :value="locale" @change="setLocale" outlined dense prepend-icon="mdi-translate">
  </v-select>
  

  <!-- IF MOBILE DEVICE, SHOW DROPDOWN MENU ON NAVBAR INSTEAD OF SELECT -->
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
.navbar-icon {
  width: 1.3rem;
  height: 1.3rem;
}
</style>
