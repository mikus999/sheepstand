<template>
  <v-app :style="{background: $vuetify.theme.themes[theme].background}">
    <NavTop @toggle-drawer="toggleDrawer" :drawer="drawer" />
    <NavLeft ref="navLeft" />
    <Snackbar></Snackbar>
    <ConfirmBox ref="confirm"></ConfirmBox>
    <NavBottom v-if="$vuetify.breakpoint.mobile && user"/>

    <!-- Sizes your content based upon application components -->
    <v-main>
      <!-- Provides the application the proper gutter -->
      <v-container fluid>
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
import axios from 'axios'
import { mapGetters, mapState } from 'vuex'
import NavTop from '../components/NavTop.vue'
import NavLeft from '../components/NavLeft.vue'
import NavBottom from '../components/NavBottom.vue'
import RouterTransition from '../components/RouterTransition.vue'

export default {
  components: {
    NavTop,
    NavLeft,
    NavBottom,
    RouterTransition
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
      theme: 'general/theme'
    }),
  },

  data() {
    return {
      drawer: false
    }
  },

  watch: {
    '$route' (to, from) {
      const siteTitle = process.env.NODE_ENV === 'production' ? 'SheepStand' : 'SheepStand Dev'
      document.title = to.meta.title ? (siteTitle + ': ' + to.meta.title) : siteTitle
    },
  },

  created () {
    // Set the dayjs locale here. Must be after the vuex store AND dayjs locales are loaded completely
    this.$dayjs.locale(this.$store.getters['lang/locale'])

    // Get site roles and permissions and save them to session storage
    if (this.user) {
      this.getRolesWithPermissions()
    }
  },

  mounted () {
    this.$root.$confirm = this.$refs.confirm.open
    this.drawer = this.$refs.navLeft.drawer
  },

  methods: {
    async getRolesWithPermissions () {
      if (!sessionStorage.getItem('roles')) {
        await axios({
          method: 'get',      
          url: '/api/roles'
        })
        .then(response => {
          sessionStorage.setItem('roles', JSON.stringify(response.data.data.roles))
        })
      }
    },

    toggleDrawer() {
      this.$refs.navLeft.drawer = !this.$refs.navLeft.drawer
      this.drawer = this.$refs.navLeft.drawer
    }
  }
}
</script>
