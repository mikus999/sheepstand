<template>
  <v-select v-if="!$vuetify.breakpoint.mobile" :items="languages" :value="locale" @change="setLocale" outlined dense prepend-icon="mdi-translate">
  </v-select>
  

  <!-- IF MOBILE DEVICE, SHOW DROPDOWN MENU ON NAVBAR INSTEAD OF SELECT -->
  <v-menu v-else bottom left>
    <template v-slot:activator="{ on, attrs }">
      <v-btn dark icon v-bind="attrs" v-on="on">
        <v-icon>mdi-translate</v-icon>
      </v-btn>
    </template>

    <v-list>
      <v-list-item v-for="l in languages" :key="l.value" @click="setLocale(l.value)">
        <v-list-item-title>{{ l.text }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>

</template>

<script>
import { loadMessages } from '~/plugins/i18n'
import helper from '../mixins/helper'

export default {
  name: 'LocaleSelector',
  mixins: [helper],

  computed: {
    languages () {
      var langArr = []
      for (const key in this.locales) {
        langArr.push({"text": this.locales[key], "value": key});
      }
      return langArr
    }
  },

  methods: {
    setLocale (locale) {
      if (this.$i18n.locale !== locale) {
        //loadMessages(locale)
        this.$i18n.locale = locale
        this.$store.dispatch('lang/setLocale', { locale })
      }
    }
  }

}
</script>

<style scoped>
.navbar-icon {
  width: 1.3rem;
  height: 1.3rem;
}
</style>
