<template>
  <v-app>
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
  },

  created () {
  }
}
</script>
