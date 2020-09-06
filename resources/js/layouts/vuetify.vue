<template>
  <v-app :style="{background: $vuetify.theme.themes[theme].background}">
    <NavLeft />

    <!-- Sizes your content based upon application components -->
    <v-main>
      <!-- Provides the application the proper gutter -->
      <v-container fluid>
        <!-- If using vue-router -->
        <router-view />
      </v-container>
    </v-main>

    <v-footer app>
    <!-- -->
    </v-footer>
  </v-app>
</template>

<script>
import NavLeft from '../components/NavLeft.vue'

export default {

  components: {
    NavLeft
  },

  computed: {
    theme () {
      return (this.$vuetify.theme.dark) ? 'dark' : 'light'
    }
  },

  created () {
  }
}
</script>
