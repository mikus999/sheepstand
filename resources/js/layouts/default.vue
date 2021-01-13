<template>
  <v-app :style="{background: $vuetify.theme.themes[theme].background}">
    <NavTop hide-sidebar />

    <!-- Sizes your content based upon application components -->
    <v-main>
      <!-- Provides the application the proper gutter -->
      <v-container fluid class="pa-0">
        <!-- If using vue-router -->
        <RouterTransition>
          <router-view />
        </RouterTransition>
      </v-container>
    </v-main>

    <v-footer class="justify-center grey lighten-1" padless height="60px">
      <v-spacer />

      <router-link :to="{ name: 'privacy' }" class="text-caption text-decoration-none mx-6">{{ $t('general.privacy_policy')}}</router-link>
      <span class="text-caption mx-6">&copy {{ $dayjs().year() }} SheepStand</span>

    </v-footer>
  </v-app>
</template>


<script>
import { mapGetters, mapState  } from 'vuex'
import NavTop from '../components/NavTop.vue'
import RouterTransition from '../components/RouterTransition.vue'

export default {
  components: {
    NavTop,
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