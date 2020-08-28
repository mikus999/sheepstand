<template>
  <v-container>
    <v-card class="w-75 mx-auto" outlined>
        <v-stepper v-model="stepperCurr">
          <v-stepper-items>

            <!-- STEP 1: JOIN OR CREATE A TEAM -->
            <v-stepper-content step="1">
              <v-row class="mt-5">
                <v-col cols=6 class="mt-5 text-center border-right">
                  <h4>{{ $t('teams.join_existing_team') }}</h4>
                  <h6 class="text-muted">
                    {{ $t('teams.enter_team_code') }}
                  </h6>
                  <v-text-field v-model="teamid" label="TM-XXXXXXXX" outlined :error="teamNotFound" :error-messages="teamNotFoundMsg"></v-text-field>
                  <v-btn color="secondary" @click.prevent="findTeam">
                    {{ $t('teams.find_team') }}
                  </v-btn>
                </v-col>

                <v-col cols=6 class="mt-5 text-center">
                  <h4>{{ $t('teams.create_new_team') }}</h4>
                  <h6 class="text-muted">
                  </h6>
                  <v-btn color="secondary" @click.prevent="nextStep(3)">
                    {{ $t('teams.get_started') }}
                  </v-btn>

                </v-col>
              </v-row>
            </v-stepper-content>


            <!-- STEP 2: VERIFY TEAM DETAILS (IF JOINING EXISTING TEAM)-->
            <v-stepper-content step="2">
              <v-row>
                <v-col cols=12 class="mt-5 text-center">
                  <h4>{{ $t('teams.confirm_team_details') }}</h4>
                  <h6 class="text-muted mb-5">
                    {{ $t('teams.confirm_team_message') }}
                  </h6>

                  <v-row>
                    <label class="col-md-5 col-form-label text-md-right">{{ $t('teams.team_id') }}</label>
                    <label class="col-md-7 col-form-label-plain text-md-left">
                      {{ team.code }}
                    </label>
                  </v-row>

                  <v-row>
                    <label class="col-md-5 col-form-label text-md-right">{{ $t('teams.team_name') }}</label>
                    <label class="col-md-7 col-form-label-plain text-md-left">
                      {{ team.name }}
                    </label>
                  </v-row>

                  <v-row>
                    <label class="col-md-5 col-form-label text-md-right">{{ $t('teams.contact_name') }}</label>
                    <label class="col-md-7 col-form-label-plain text-md-left">
                      {{ teamcontact.name }}
                    </label>
                  </v-row>

                  <v-row>
                    <label class="col-md-5 col-form-label text-md-right">{{ $t('teams.contact_email') }}</label>
                    <label class="col-md-7 col-form-label-plain text-md-left">
                      {{ teamcontact.email }}
                    </label>
                  </v-row>

                  <br><br>
                  
                  <v-btn id="cancel" @click.prevent="clearForm">
                    {{ $t('general.go_back') }}
                  </v-btn>

                  <v-btn color="secondary" @click.prevent="joinTeam">
                    {{ $t('teams.join_team') }}
                  </v-btn>
                </v-col>
              </v-row>
            </v-stepper-content>


            <!-- STEP 3: ENTER NEW TEAM NAME (IF CREATING NEW TEAM)-->
            <v-stepper-content step="3">
              <v-row>
                <v-col cols=12 class="mt-5 text-center">
                  <h4 class="ma-3">{{ $t('teams.new_team_name') }}</h4>
                  <h6 class="text-muted ma-3">
                    {{ $t('teams.new_team_name_help') }}
                  </h6>

                  <v-text-field v-model="team_name" class="ma-3" :label="$t('teams.team_name')" outlined :error="teamNameError" :error-messages="teamNameErrorMsg"></v-text-field>

                  <v-btn id="cancel" @click.prevent="clearForm">
                    {{ $t('general.go_back') }}
                  </v-btn>
                  <v-btn color="secondary" @click.prevent="createTeam">
                    {{ $t('general.create') }}
                  </v-btn>
                </v-col>
              </v-row>
            </v-stepper-content>

            
            <!-- STEP 4: SUCCESS CONFIRMATION-->
            <v-stepper-content step="4">
              <v-row>
                <v-col cols=12 class="mt-5 text-center">
                  <v-icon size="120" color="success">mdi-check-circle-outline</v-icon>
                  <h1 class="display-3 ma-10">{{ successMsg }}</h1>
                </v-col>
              </v-row>
            </v-stepper-content>
          </v-stepper-items>
        </v-stepper>
    </v-card>
  </v-container>
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
      teamNotFoundMsg: '',
      team_name: '',
      teamNameError: false,
      teamNameErrorMsg: '',
      teamFound: false,
      successMsg: this.$t('teams.welcome_to_team'),
      team: [],
      teamcontact: [],
      stepperCurr: 1,
      stepperMax: 4,
    }
  },

  created () {
  },

  methods: {
    nextStep (n) {
      if (n !== this.stepperMax) {
        this.stepperCurr = n
      }
    },

    async findTeam () {
      this.teamNotFound = false
      this.teamFound = false

      if (this.teamid !== '') {
        await axios.get('/api/teams/findteam/' + this.teamid)
          .then(response => {
            if (response.data.message === 'NOT_FOUND') {
              this.teamNotFound = true
              this.teamNotFoundMsg = this.$t('teams.team_not_found')
              this.teamFound = false
            } else {
              this.teamNotFound = false
              this.teamNotFoundMsg = ''
              this.teamFound = true
              this.team = response.data.team
              this.teamcontact = response.data.user
              this.stepperCurr = 2
            }
          })
      }
    },

    joinTeam () {
      const formData = new FormData()
      formData.append('user_id', this.userid)
      formData.append('team_id', this.teamid)
      axios.post('/api/teams/jointeam', formData)
        .then(response => {
          this.stepperCurr = 4
        })
    },

    createTeam () {
      this.hasError = false

      const formData = new FormData()
      formData.append('name', this.team_name)
      axios.post('/api/teams/', formData)
        .then(response => {
          if (response.data.message === 'ERROR') {
            this.teamNameError = true
            this.teamNotFoundMsg = this.$t('teams.error_creating_team')
          } else {
            this.getTeams()
            this.stepperCurr = 4
          }
        })
    },

    async getTeams () {
      await this.$store.dispatch('teams/fetchTeams')
    },

    clearForm () {
      this.teamNotFound = false
      this.teamNotFoundMsg = ''
      this.teamNameError = false
      this.teamNameErrorMsg = ''
      this.teamFound = false
      this.team = []
      this.teamcontact = []
      this.teamid = ''
      this.stepperCurr = 1
    }
  }
}
</script>
