<template>
  <v-app :style="{background: $vuetify.theme.themes[theme].background}">
    <NavTop v-show="$vuetify.breakpoint.mobile" @toggle-drawer="$refs.drawer.drawer = !$refs.drawer.drawer" />
    <NavLeft ref="drawer" />
    <NavBottom v-show="$vuetify.breakpoint.mobile && user"/>

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
import { mapGetters, mapState } from 'vuex'
import NavTop from '../components/NavTop.vue'
import NavLeft from '../components/NavLeft.vue'
import NavBottom from '../components/NavBottom.vue'

export default {

  components: {
    NavTop,
    NavLeft,
    NavBottom
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
    }),

    theme () {
      return (this.$vuetify.theme.dark) ? 'dark' : 'light'
    }
  },

  created () {
  }
}
</script>
