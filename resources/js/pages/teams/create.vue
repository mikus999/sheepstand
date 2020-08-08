<template>
<div id="main-content">
    <div class="main-content__title">
    </div>
    <div class="main-content__body">
      <div class="card w-75 mx-auto">
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 text-center p-5">
              <h1 class="display-4">
                Create a new team
              </h1>

              <div class="row mt-5" />
              <form @submit.prevent="joinTeam">
                <h4>Give your team a name</h4>
                <h6 class="text-muted">
                  This might be the name of your congregation, special metropolitan witnessing program, service group, etc.
                </h6>
                <input v-model="team_name" class="form-control form-control-lg text-center mt-5" type="text" placeholder="Team Name">
                <h6 v-if="hasError" class="text-danger m-2">
                  There was an error while creating the team.
                </h6>
                <br>
                <button class="btn btn-primary">
                  Create
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
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
