<template>
  <div id="wrapper" :class="wrapperClass">
    <MenuToggleBtn />
    <Menu />
    <ContentOverlay />

    <div class="main-content">
      <router-view />
    </div>
  </div>
</template>

<script>
import MenuToggleBtn from '../components/MenuToggleBtn.vue'
import Menu from '../components/Menu.vue'
import ContentOverlay from '../components/ContentOverlay.vue'
import { mapGetters } from 'vuex'

export default {

  components: {
    MenuToggleBtn,
    Menu,
    ContentOverlay
  },

  data () {
    return {
      isOpenMobileMenu: false
    }
  },

  computed: {
    wrapperClass () {
      return {
        'toggled': this.isOpenMobileMenu === true
      }
    }
  },

  created () {
    window.bus.$on('menu/toggle', () => {
      window.setTimeout(() => {
        this.isOpenMobileMenu = !this.isOpenMobileMenu
      }, 200)
    })

    window.bus.$on('menu/closeMobileMenu', () => {
      this.isOpenMobileMenu = false
    })

  }
}
</script>
