<template>
  <div id="main-content">
    <v-card class="w-75 mx-auto" outlined>
      <div class="row">
        <div class="col-md-12 text-center p-5">
          <h1 class="display-3">
            Become part of a team
          </h1>

          <div v-if="!teamFound" class="row mt-5">
            <div class="col-md-6 mt-5 border-right">
              <form @submit.prevent="findTeam">
                <h4>Join an existing team</h4>
                <h6 class="text-muted">
                  Enter the code given to you by your administrator
                </h6>
                <input v-model="teamid" :class="{ 'is-invalid': teamNotFound}" class="form-control form-control-lg text-center text-uppercase mt-5" type="text" placeholder="TM-XXXXXXXX">
                <h6 v-if="teamNotFound" class="text-danger m-2">
                  {{ $t('team_not_found') }}
                </h6>

                <br>
                <v-btn color="secondary" type="submit">
                  Find Team
                </v-btn>
                <hr class="d-lg-none mt-5">
              </form>
            </div>

            <div class="col-md-6 mt-5">
              <h4>Create a new team</h4>
              <br>
              <v-btn color="secondary" :to="{ name: 'teams.create' }">
                Get Started
              </v-btn>
            </div>
          </div>

          <div v-else class="row mt-5">
            <div class="col-md-12 mt-5">
              <form @submit.prevent="joinTeam">
                <h4>Confirm Team Details</h4>
                <h6 class="text-muted mb-5">
                  The following team was found with the code provided. Do you want to join this team?
                </h6>

                <div class="row">
                  <label class="col-md-5 col-form-label text-md-right">Team ID</label>
                  <label class="col-md-7 col-form-label-plain text-md-left">
                    {{ team.code }}
                  </label>
                </div>

                <div class="row">
                  <label class="col-md-5 col-form-label text-md-right">Team Name</label>
                  <label class="col-md-7 col-form-label-plain text-md-left">
                    {{ team.name }}
                  </label>
                </div>

                <div class="row">
                  <label class="col-md-5 col-form-label text-md-right">Contact Name</label>
                  <label class="col-md-7 col-form-label-plain text-md-left">
                    {{ teamcontact.name }}
                  </label>
                </div>

                <div class="row">
                  <label class="col-md-5 col-form-label text-md-right">Contact Email</label>
                  <label class="col-md-7 col-form-label-plain text-md-left">
                    {{ teamcontact.email }}
                  </label>
                </div>

                <v-btn tton id="cancel" class="mt-5" @click.prevent="clearForm">
                  Go Back
                </v-btn>

                <v-btn color="secondary" class="mt-5">
                  Join Team
                </v-btn>
              </form>
            </div>
          </div>
        </div>
      </div>
    </v-card>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  middleware: 'auth',
  layout: 'vuetify',

  data () {
    return {
      userid: this.$userId,
      teamid: '',
      teamNotFound: false,
      teamFound: false,
      team: [],
      teamcontact: []
    }
  },

  created () {
  },

  methods: {
    async findTeam () {
      this.teamNotFound = false
      this.teamFound = false

      await axios.get('/api/teams/findteam/' + this.teamid)
        .then(response => {
          if (response.data.message === 'NOT_FOUND') {
            this.teamNotFound = true
            this.teamFound = false
          } else {
            this.teamNotFound = false
            this.teamFound = true
            this.team = response.data.team
            this.teamcontact = response.data.user
          }
        })
    },

    joinTeam () {
      const formData = new FormData()
      formData.append('user_id', this.userid)
      formData.append('team_id', this.teamid)
      axios.post('/api/teams/jointeam', formData)
        .then(response => {
          if (this.$route.name === 'home') {
            this.$emit('joinedTeam')
          } else {
            this.$router.push('/home')
          }
        })
    },

    clearForm () {
      this.teamNotFound = false
      this.teamFound = false
      this.team = []
      this.teamcontact = []
      this.teamid = ''
    }
  }
}
</script>
