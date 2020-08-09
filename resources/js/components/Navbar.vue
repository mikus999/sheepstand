<template>
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <router-link :to="{ name: user ? 'home' : 'welcome' }" class="navbar-brand">
        <img src="logo.png" width="30" height="30" class="d-inline-block align-top mr-1">
        <span class="head-thick">CART</span>
        <span class="head-thin ml-n1">PLAN</span>

        <span v-if="environment==='local'" class="head-thin ml-1 text-danger">DEV</span>
        <span v-else class="head-thin ml-1 text-danger">BETA</span>
      </router-link>

      <button class="navbar-toggler nav-link" type="button" data-toggle="collapse" data-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false">
        <!--<span class="navbar-toggler-icon" />-->
        <fa icon="bars" class="navbar-icon" fixed-width />
      </button>

      <div id="navbarToggler" class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
          <router-link v-if="user" :to="{ name: 'home' }" class="nav-link">
            <fa icon="tachometer-alt" class="navbar-icon d-lg-none" fixed-width />
            <span class="ml-2">Dashboard</span>
          </router-link>
          <router-link v-if="user" :to="{ name: 'home' }" class="nav-link">
            <fa icon="cogs" class="navbar-icon d-lg-none" fixed-width />
            <span class="ml-2">Administration</span>
          </router-link>
        </ul>

        <ul class="navbar-nav ml-auto">
          <!-- Language changer -->
          <locale-dropdown />

          <!-- Authenticated -->
          <li v-if="user" class="nav-item dropdown">
            <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img :src="user.photo_url" class="rounded-circle navbar-icon profile-photo">
              <span class="d-lg-none ml-2">{{ user.name }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
              <span class="dropdown-item pl-3 disabled d-none d-lg-block">{{ user.name }}</span>
              <div class="dropdown-divider d-none d-lg-block" />

              <router-link :to="{ name: 'settings.profile' }" class="dropdown-item pl-3">
                <fa icon="cog" fixed-width />
                {{ $t('settings') }}
              </router-link>

              <a href="#" class="dropdown-item pl-3" @click.prevent="logout">
                <fa icon="sign-out-alt" fixed-width />
                {{ $t('logout') }}
              </a>
            </div>
          </li>

          <!-- Guest -->
          <template v-else>
            <li class="nav-item">
              <router-link :to="{ name: 'login' }" class="nav-link" active-class="active">
                <fa icon="sign-in-alt" class="navbar-icon" fixed-width />
                <span class="d-lg-none ml-2">{{ $t('login') }}</span>
              </router-link>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import { mapGetters } from 'vuex'
import LocaleDropdown from './LocaleDropdown'

export default {
  components: {
    LocaleDropdown
  },

  data: () => ({
    appName: window.config.appName,
    environment: window.config.environment
  }),

  computed: mapGetters({
    user: 'auth/user'
  }),

  methods: {
    async logout () {
      // Log out the user.
      await this.$store.dispatch('auth/logout')

      // Redirect to login.
      this.$router.push({ name: 'login' })
    }
  }

}
</script>

<style scoped>
.profile-photo {
  background-color: #ffffff;
  margin-top: -0.2em;
}

.navbar-icon {
  width: 1.3rem;
  height: 1.3rem;
}
</style>
