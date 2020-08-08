<template>
  <div class="main-content">
    <div id="mobile-top-bar" class="main-content__top">

      <a href="#" class="btn menu-toggle-btn" @click.prevent="toggleMenu">
        <fa id="menu-toggle-btn" icon="bars" aria-hidden="true" />
      </a>

      <h1 class="main-content__title">
        <router-link :to="{ name: user ? 'home' : 'welcome' }" class="navbar-brand">
          <img src="dist/img/cplogo.png" width="30" height="30" class="d-inline-block align-middle">

          <span class="head-thick">CART</span>
          <span class="head-thin ml-n1">PLAN</span>
        </router-link>
      </h1>
    </div>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  computed: {
    ...mapGetters({
      user: 'auth/user'
    })
  },

  methods: {

    toggleMenu () {
      window.bus.$emit('menu/toggle')
    }

  }

}

</script>
