<template>
  <div class="menu-container">
    <!-- root level itens -->
    <ul class="menu">
      <li class="menu__top">
        <router-link :to="{ name: user ? 'home' : 'welcome' }" class="navbar-brand">
          <img src="dist/img/cplogo.png" width="30" height="30" class="d-inline-block align-top ml-1 mr-2">

          <span class="head-thick">CART</span>
          <span class="head-thin ml-n1">PLAN</span>
        </router-link>
      </li>

      <li>
        <a href="#" :class="highlightSection('home')" @click.prevent="updateMenu('home')">
          <fa icon="home" class="menu__icon" fixed-width aria-hidden="true" />
          Home
        </a>
      </li>

      <li>
        <a href="#" :class="highlightSection('account')" @click.prevent="updateMenu('account')">
          <fa icon="user" class="menu__icon" fixed-width aria-hidden="true" />
          Account
          <fa icon="chevron-right" class="menu__arrow-icon" aria-hidden="true" />
        </a>
      </li>

      <li>
        <a href="#" :class="highlightSection('teams')" @click.prevent="openTeamsMenu('teams')">
          <fa icon="users" class="menu__icon" fixed-width aria-hidden="true" />
          {{ shortenString(formatJSON(team).name) }}

          <fa icon="chevron-right" class="menu__arrow-icon" aria-hidden="true" />
        </a>
      </li>
    </ul>

    <!-- context menu: childs of root level itens -->
    <transition name="slide-fade">
      <div v-show="showContextMenu" class="context-menu-container">
        <ul class="context-menu">
          <li v-for="(item, index) in menuItems" :key="index">
            <h5 v-if="item.type === 'title'" class="context-menu__title">
              <i :class="item.icon" aria-hidden="true" />

              {{ item.txt }}

              <a v-if="index === 0" class="context-menu__btn-close" href="#" @click.prevent="closeContextMenu">
                <fa icon="chevron-left" aria-hidden="true" />
              </a>
            </h5>

            <a v-else href="#" :class="subMenuClass(item.txt)" @click.prevent="openSection(item)">
              {{ item.txt }}
            </a>
          </li>
        </ul>
      </div>
    </transition>

    <!-- context menu: FOR TEAMS-->
    <transition name="slide-fade">
      <div v-show="showTeamsMenu" class="context-menu-container">
        <ul class="context-menu">
          <li>
            <h5 class="context-menu__title">
              Actions
              <a class="context-menu__btn-close" href="#" @click.prevent="closeTeamsMenu">
                <fa icon="chevron-left" aria-hidden="true" />
              </a>
            </h5>
          </li>
          <li>
            <router-link class="context-menu__link" :to="{ name: 'teams.index' }">
              Team Settings
            </router-link>
          </li>
          <li>
            <router-link class="context-menu__link" :to="{ name: 'teams.join' }">
              Join New Team
            </router-link>
          </li>
          
          <li v-show="hasTeam">
            <h5 class="context-menu__title">
              Change Team
            </h5>
          </li>

          <li v-show="hasTeam" v-for="t in teams" :key="t.id" >
            <a href="#" :class="subMenuClass(t.name)" @click.prevent="setTeam(t.id)">
              {{ t.name }}
            </a>
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>

<script>
import menuData from './support/menu-data'
import kebabCase from 'lodash/kebabCase'
import { mapGetters } from 'vuex'

export default {
  name: 'Menu',

  components: {
  },

  data () {
    return {
      contextSection: '',
      menuItems: [],
      menuData: menuData,
      activeSubMenu: '',
      showTeamsMenu: false
    }
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
      team: 'teams/getTeam',
      teams: 'teams/getTeams',
      hasTeam: 'teams/hasTeam'
    }),
    showContextMenu () {
      return this.menuItems.length
    }
  },

  created () {
    this.getTeams()
  },

  methods: {

    openProjectLink () {
      alert('You could open the project frontend in another tab here, so the logged admin could see changes made to the project ;)')
    },

    updateMenu (context) {
      this.showTeamsMenu = false
      this.contextSection = context
      this.menuItems = this.menuData[context]

      if (context === 'home') {
        this.$router.push('/home')
        window.bus.$emit('menu/closeMobileMenu')
      }
    },

    openTeamsMenu (context) {
      this.contextSection = context
      this.showTeamsMenu = true
    },

    closeTeamsMenu () {
      this.closeContextMenu()
    },

    highlightSection (section) {
      return {
        'menu__link': true,
        'menu__link--active': section === this.contextSection
      }
    },

    subMenuClass (subMenuName) {
      return {
        'context-menu__link': true,
        'context-menu__link--active': this.activeSubMenu === subMenuName
      }
    },

    closeContextMenu () {
      this.contextSection = ''
      this.menuItems = []
      this.showTeamsMenu = false
    },


    openSection (item) {
      this.activeSubMenu = item.txt

      this.$router.push(this.getUrl(item))
      window.bus.$emit('menu/closeMobileMenu')
    },

    getUrl (item) {
      if (item.link === '/logout') {
        this.logout()
      } else {
        return `${item.link}`
      }
    },

    async logout () {
      // Log out the user.
      await this.$store.dispatch('auth/logout')

      // Redirect to login.
      this.$router.push({ name: 'login' })
    },

    setTeam (teamid) {
      this.$store.dispatch('teams/setTeam', { teamid })
      this.getTeams()
      this.closeTeamsMenu()

      if (this.$router.currentRoute.name !== 'home') {
        this.$router.push('/home')
      }
    },

    async getTeams () {
      await this.$store.dispatch('teams/fetchTeams')
    },
    
    formatJSON (data) {
      if (data.name) {
        return JSON.parse(JSON.stringify(data))
      } else {
        return JSON.parse(data)
      }
    },

    shortenString (string, chars) {
      if (string) {
        return string.slice(0, chars)
      }
    }

  }

}
</script>
