<template>
  <div v-if="hasTeam" class="dropdown">
    <a id="dropdownMenuLink" class="btn btn-primary dropdown-toggle w-100 text-left border-0" href="#" role="button"
       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
    >
      <fa icon="users" class="menu__icon" aria-hidden="true" />
      {{ (formatJSON(team).name).slice(0,18) }}
    </a>

    <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuLink">
      <a v-for="t in teams" :key="t.id" class="dropdown-item" href="#" @click.prevent="setTeam(t.id)">
        {{ t.name }}
      </a>
      <div class="dropdown-divider"></div>
      <router-link class="dropdown-item" :to="{ name: 'teams.join' }">
        Join Team
      </router-link>
    </div>
  </div>

  <div v-else>
    <router-link class="menu__link" :to="{ name: 'teams.join' }">
      <fa icon="users" class="menu__icon" aria-hidden="true" />
      Join Team
    </router-link>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'
import Cookies from 'js-cookie'

export default {
  name: 'TeamSelector',

  data: () => ({

  }),

  computed: {
    ...mapGetters({
      team: 'teams/getTeam',
      teams: 'teams/getTeams',
      hasTeam: 'teams/hasTeam'
    })
  },

  mounted () {
    // console.log(this.$store.getters['teams/getTeam'])
  },

  methods: {
    setTeam (teamid) {
      this.$store.dispatch('teams/setTeam', { teamid })
    },

    getTeamInfo () {
      axios.get('/api/teams')
        .then(response => {
          // this.currteam = response.data.teams[0]
        })
    },

    formatJSON (data) {
      if (data.name) {
        return JSON.parse(JSON.stringify(data))
      } else {
        return JSON.parse(data)
      }
    }
  }

}
</script>
