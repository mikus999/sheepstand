<template>
  <v-container fluid>
    <v-card class="mx-auto" max-width="600" outlined>
        <v-stepper v-model="stepperCurr">
          <v-stepper-items>

            <!-- STEP 1: JOIN OR CREATE A TEAM -->
            <v-stepper-content step="1">
              <v-row class="mt-5">
                <v-col cols=12 class="text-center">
                  <h3>{{ $t('teams.join_existing_team') }}</h3>
                  <blockquote class="blockquote text-muted">
                    {{ $t('teams.what_is_a_team') }}
                  </blockquote>
                </v-col>
              </v-row>

              <v-row class="mt-2">
                <v-col cols=12 class="text-center">
                  <h6 class="mt-6">
                    {{ $t('teams.enter_team_code') }}
                  </h6>
                  <v-text-field v-model="teamid" label="TM-XXXXXXXX" outlined :error="teamNotFound" :error-messages="teamNotFoundMsg"></v-text-field>
                  <v-btn color="secondary" @click.prevent="findTeam">
                    {{ $t('teams.find_team') }}
                  </v-btn>
                </v-col>
              </v-row>

              <v-divider class="my-13"></v-divider>

              <v-row class="mb-6">
                <v-col cols=12 class="text-center">
                  <h6 class="text-muted">
                    <a @click.prevent="nextStep(3, $t('teams.confirm_create_new_team'))">
                      {{ $t('teams.create_new_team') }}
                    </a>
                  </h6>
                </v-col>
              </v-row>
            </v-stepper-content>


            <!-- STEP 2: VERIFY TEAM DETAILS (IF JOINING EXISTING TEAM)-->
            <v-stepper-content step="2">
              <v-row>
                <v-col cols=12 class="mt-5 text-center">
                  <h3>{{ $t('teams.confirm_team_details') }}</h3>
                  <h6 class="text-muted mb-5">
                    {{ $t('teams.confirm_team_message') }}
                  </h6>

                  <v-row>
                    <label class="col-md-5 col-form-label text-md-right">{{ $t('teams.team_id') }}</label>
                    <label class="col-md-7 col-form-label-plain text-md-left">
                      {{ teamTemp.code }}
                    </label>
                  </v-row>

                  <v-row>
                    <label class="col-md-5 col-form-label text-md-right">{{ $t('teams.team_name') }}</label>
                    <label class="col-md-7 col-form-label-plain text-md-left">
                      {{ teamTemp.display_name }}
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
                  <h3 class="ma-3">{{ $t('teams.new_team_name') }}</h3>
                  <blockquote class="blockquote text-muted">
                    {{ $t('teams.new_team_name_help') }}
                  </blockquote>

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
                  <v-icon size="100" color="success">mdi-check-circle-outline</v-icon>
                  <p class="ma-10"><span class="display-2">{{ successMsg }}</span></p>
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
import helper from '~/mixins/helper'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

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
      teamTemp: [],
      teamcontact: [],
      stepperCurr: 1,
      stepperMax: 4,
    }
  },

  created () {
  },

  methods: {
    nextStep (n, msg) {
      var isNextStep = true
      console.log(msg)

      if (msg !== undefined) {
        isNextStep = confirm(msg)
      }

      if (isNextStep) {
        if (n !== this.stepperMax) {
          this.stepperCurr = n
        }
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
              this.teamTemp = response.data.team
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
      formData.append('display_name', this.team_name)
      axios.post('/api/teams/', formData)
        .then(response => {
          if (response.data.message === 'ERROR') {
            this.teamNameError = true
            this.teamNotFoundMsg = this.$t('teams.error_creating_team')
          } else {
            this.getTeams()

            this.setTeam(response.data.team)

            this.stepperCurr = 4
          }
        })
    },


    clearForm () {
      this.teamNotFound = false
      this.teamNotFoundMsg = ''
      this.teamNameError = false
      this.teamNameErrorMsg = ''
      this.teamFound = false
      this.teamTemp = []
      this.teamcontact = []
      this.teamid = ''
      this.stepperCurr = 1
    }
  }
}
</script>
