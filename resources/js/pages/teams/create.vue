<template>
  <v-card class="w-75 mx-auto" outlined>
    <div class="row">
      <div class="col-md-12 text-center p-5">
        <h1 class="display-3">
          Create a new team
        </h1>

        <form @submit.prevent="joinTeam">
          <h4 class="mt-5">Give your team a name</h4>
          <h6 class="text-muted">
            This might be the name of your congregation, special metropolitan witnessing program, service group, etc.
          </h6>
          <input v-model="team_name" class="form-control form-control-lg text-center mt-5" type="text" placeholder="Team Name">
          <h6 v-if="hasError" class="text-danger m-2">
            There was an error while creating the team.
          </h6>
          <br>
          <v-btn color="secondary" type="submit">
            Create
          </v-btn>
        </form>

      </div>
    </div>
  </v-card>
</template>

<script>
import axios from 'axios'

export default {
  middleware: 'auth',
  layout: 'vuetify',

  data () {
    return {
      hasError: false,
      team_name: ''
    }
  },

  methods: {
    joinTeam () {
      this.hasError = false

      const formData = new FormData()
      formData.append('name', this.team_name)
      axios.post('/api/teams/', formData)
        .then(response => {
          if (response.data.message === 'ERROR') {
            this.hasError = true
          } else {
            this.getTeams()
            this.$router.push('/home')
          }
        })
    },

    async getTeams () {
      await this.$store.dispatch('teams/fetchTeams')
    }
  }
}
</script>
