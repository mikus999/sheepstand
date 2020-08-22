<template>
  <v-navigation-drawer class="navleft-main" app>
    <v-list flat>
        <v-list-item>
          <v-list-item-content>
            <v-list-item-title class="title">
              <svg width="25" height="25" version="1.1" fill="currentColor" viewBox="0 0 12.7 12.7" xmlns="http://www.w3.org/2000/svg">
              <g transform="scale(.98535 1.0149)" aria-label="C">
                <path d="m11.445 9.3097q-1.6102 2.0742-4.4213 2.0742-2.3744 0-3.8891-1.501-1.5147-1.501-1.5147-3.7936 0-1.4192 0.69594-2.62 0.70959-1.2008 1.9514-1.8831 1.2418-0.68229 2.6746-0.68229 1.4465 0 2.5791 0.55948 1.1463 0.54584 1.9104 1.6102l-0.90063 0.69594q-1.4192-1.7194-3.507-1.7194-1.7057 0-2.9475 1.2008-1.2281 1.2008-1.2281 2.9475 0 1.733 1.2008 2.9066 1.2145 1.1599 3.1386 1.1599t3.3432-1.6512z"/>
              </g>
              <path transform="scale(.26458)" d="m22.486 10.08v15.014h3.2988v-0.009766h12.992v-3.2656h-12.992v-11.738h-3.2988z"/>
              </svg>

              <span class="head-thick">CART</span>
              <span class="head-thin ml-n1">PLAN</span>
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>

        <v-divider />

        <v-list-item router :to="{ name: 'home' }" class="text-decoration-none">
          <v-list-item-icon>
            <v-icon>mdi-view-dashboard</v-icon>
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title>Dashboard</v-list-item-title>
          </v-list-item-content>
        </v-list-item>

        <!-- TEAMS -->
        <v-list-group prepend-icon="mdi-account-group" no-action>
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                {{ hasTeam ? formatJSON(team).name : "Teams"}}
              </v-list-item-title>
            </v-list-item-content>
          </template>

          <v-subheader inset>Teams</v-subheader>

          <v-list-item v-for="t in teams" :key="t.id" item-value="true" @click.prevent="setTeam(t.id)" >
            <v-list-item-content>
              <v-list-item-title v-text="t.name" />
            </v-list-item-content>
          </v-list-item>

          <v-divider inset />
          <v-subheader inset>Actions</v-subheader>

          <v-list-item router :to="{ name: 'teams.join' }" class="text-decoration-none">
            <v-list-item-title>Join Team</v-list-item-title>
          </v-list-item>

          <v-list-item router :to="{ name: 'teams.index' }" class="text-decoration-none" v-if="hasTeam">
            <v-list-item-title>Team Settings</v-list-item-title>
          </v-list-item>
        </v-list-group>

        <!-- SCHEDULING -->
        <v-list-group prepend-icon="mdi-calendar" v-if="hasTeam" no-action>
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>
                Scheduling
              </v-list-item-title>
            </v-list-item-content>
          </template>

          <v-list-item router :to="{ name: 'schedules.index' }" class="text-decoration-none">
            <v-list-item-title>Cart Schedules</v-list-item-title>
          </v-list-item>

        </v-list-group>


        <!-- ACCOUNT -->
        <v-list-group prepend-icon="mdi-account" no-action>
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title>Account</v-list-item-title>
            </v-list-item-content>
          </template>

        </v-list-group>

      </v-list>

      <template v-slot:append>
        <div class="pa-2">
          <v-switch v-model="$vuetify.theme.dark" hide-details inset label="Dark Theme" class="pb-4"></v-switch>

          <v-btn block @click.prevent="logout">Logout</v-btn>
        </div>
      </template>
  </v-navigation-drawer>
</template>

<script>
import { mapGetters, mapState } from 'vuex'
import axios from 'axios'
import Cookies from 'js-cookie'

export default {
  data () {
    return {
    }
  },

  computed: {
    ...mapGetters({
      team: 'teams/getTeam',
      teams: 'teams/getTeams',
      hasTeam: 'teams/hasTeam'
    })
  },

  created () {
    this.getTeams()
  },

  methods: {
    setTeam (teamid) {
      this.$store.dispatch('teams/setTeam', { teamid })
      this.getTeams()

      if (this.$router.currentRoute.name !== 'home') {
        this.$router.push('/home')
      }
    },

    getTeamInfo () {
      axios.get('/api/teams')
        .then(response => {
          // this.currteam = response.data.teams[0]
        })
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

    async logout () {
      // Log out the user.
      await this.$store.dispatch('auth/logout')

      // Redirect to login.
      this.$router.push({ name: 'login' })
    }
  
  },

  toggleDarkMode () {
    this.$vuetify.theme.dark = !this.$vuetify.theme.dark
  }
}
</script>
