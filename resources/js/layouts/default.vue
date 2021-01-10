<template>
  <v-app :style="{background: $vuetify.theme.themes[theme].background}">
    <NavTopDefault />

    <!-- Sizes your content based upon application components -->
    <v-main>
      <!-- Provides the application the proper gutter -->
      <v-container fluid class="pa-0 fill-height">
        <!-- If using vue-router -->
        <RouterTransition>
          <router-view />
        </RouterTransition>
      </v-container>
    </v-main>

    <v-footer app>
    <!-- -->
    </v-footer>
  </v-app>
</template>


<script>
import { mapGetters, mapState  } from 'vuex'
import NavTopDefault from '../components/NavTopDefault.vue'
import RouterTransition from '../components/RouterTransition.vue'

export default {
  components: {
    NavTopDefault,
    RouterTransition
  },

  computed: {
    ...mapGetters({
      //user: 'auth/user',
      theme: 'general/theme'
    }),
  },

  watch: {
    '$route' (to, from) {
      const siteTitle = process.env.NODE_ENV === 'production' ? 'SheepStand' : 'SheepStand Dev'
      document.title = to.meta.title ? (siteTitle + ': ' + to.meta.title) : siteTitle
    }
  },

  created () {
    // Set the dayjs locale here. Must be after the vuex store AND dayjs locales are loaded completely
    this.$dayjs.locale(this.$store.getters['lang/locale'])

  },


}
</script>