<template>
<v-container fluid>
  <v-card class="mx-auto" max-width="600" outlined>
    <v-card-title>
      <v-icon left>mdi-account-multiple-plus</v-icon>
      {{ $t('teams.join_existing_team') }}
    </v-card-title>

    <v-card-text>
      <v-stepper v-model="stepperCurr">
        <v-stepper-items>

          <!-- STEP 1: JOIN OR CREATE A TEAM -->
          <v-stepper-content step="1">
            <v-row class="mt-5">
              <v-col cols=12 class="text-center">
                <h3>{{ $t('teams.what_is_a_team') }}</h3>
                <p class="text-muted my-8">
                  {{ $t('teams.team_explanation') }}
                </p>
                <p class="text-muted my-8 font-weight-bold">
                  {{ $t('teams.enter_team_code') }}
                </p>

                <v-text-field v-model="teamid" class="my-16" label="TM-XXXXXXXX" outlined :error="teamNotFound" :error-messages="teamNotFoundMsg"></v-text-field>

                <v-btn color="primary" class="mb-8 mt-n8" @click.prevent="findTeam">
                  {{ $t('teams.find_team') }}
                </v-btn>
              </v-col>
            </v-row>

            <v-divider class="my-4"></v-divider>

            <v-row>
              <v-col cols=12 class="text-center">
                <p class="text-muted my-8">
                  <a @click.prevent="nextStep(3, $t('teams.confirm_create_new_team'))">
                    {{ $t('teams.create_new_team') }}
                  </a>
                </p>
              </v-col>
            </v-row>
          </v-stepper-content>

          <!-- STEP 2: VERIFY TEAM DETAILS (IF JOINING EXISTING TEAM)-->
          <v-stepper-content step="2">
            <v-row>
              <v-col cols=12 class="mt-5 text-center">
                <h3>{{ $t('teams.confirm_team_details') }}</h3>
                <p class="text-muted my-8">
                  {{ $t('teams.confirm_team_message') }}
                </p>

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

                <!--
                  <v-row>
                    <label class="col-md-5 col-form-label text-md-right">{{ $t('teams.contact_email') }}</label>
                    <label class="col-md-7 col-form-label-plain text-md-left">
                      {{ teamcontact.email }}
                    </label>
                  </v-row>
                  -->

                <br><br>

                <v-btn text id="cancel" @click.prevent="clearForm">
                  {{ $t('general.go_back') }}
                </v-btn>

                <v-btn color="primary" @click.prevent="joinTeam">
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
                <p class="text-muted my-8">
                  {{ $t('teams.new_team_name_help') }}
                </p>

                <v-text-field v-model="team_name" class="ma-3" :label="$t('teams.team_name')" outlined :error="teamNameError" :error-messages="teamNameErrorMsg"></v-text-field>

                <v-btn text id="cancel" @click.prevent="clearForm">
                  {{ $t('general.go_back') }}
                </v-btn>
                <v-btn color="primary" @click.prevent="createTeam">
                  {{ $t('general.create') }}
                </v-btn>
              </v-col>
            </v-row>
          </v-stepper-content>

          <!-- STEP 4: SUCCESS CONFIRMATION-->
          <v-stepper-content step="4">
            <v-row>
              <v-col cols=12 class="mt-5 text-center">
                <h1 class="ma-3">
                  <v-icon size="30" color="success">mdi-check-circle-outline</v-icon>
                  {{ this.$t('teams.welcome_to_team') }}
                </h1>

                <v-divider class="my-16" />

                <div class="text-muted my-8">

                  <!-- Tasks for team administrator -->
                  <div v-if="isNewTeam">
                    <h3>{{ $t('notifications.setup_notifications_now') }}</h3>

                    <p class="text-muted my-8">
                      {{ $t('notifications.feature_explanation_team')}}
                    </p>

                    <v-btn text id="cancel" router :to="{ name: 'teams.index' }">
                      {{ $t('general.later') }}
                    </v-btn>
                    <v-btn color="primary" router :to="{ name: 'notifications.setup' }">
                      {{ $t('notifications.setup_now') }}
                    </v-btn>
                  </div>

                  <!-- Tasks for new team member -->
                  <div v-if="!isNewTeam">
                    <h3>{{ $t('notifications.subscribe_notifications_now') }}</h3>

                    <p class="text-muted my-8">
                      {{ $t('notifications.feature_explanation_user')}}
                    </p>

                    <v-btn text id="cancel" router :to="{ name: 'home' }">
                      {{ $t('general.later') }}
                    </v-btn>
                    <v-btn color="primary" router :to="{ name: 'notifications.join' }">
                      {{ $t('notifications.subscribe_now') }}
                    </v-btn>
                  </div>
                </div>
              </v-col>
            </v-row>
          </v-stepper-content>

        </v-stepper-items>
      </v-stepper>
    </v-card-text>
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

  data() {
    return {
      teamid: '',
      teamNotFound: false,
      teamNotFoundMsg: '',
      team_name: '',
      teamNameError: false,
      teamNameErrorMsg: '',
      teamFound: false,
      teamTemp: [],
      teamcontact: [],
      stepperCurr: 1,
      stepperMax: 4,
      isNewTeam: false,
    }
  },

  created() {},

  methods: {
    async nextStep(n, msg) {
      var isNextStep = true
      console.log(msg)

      if (msg !== undefined) {
        isNextStep = await this.$root.$confirm(msg, null, 'primary')
      }

      if (isNextStep) {
        if (n !== this.stepperMax) {
          this.stepperCurr = n
        }
      }
    },

    async findTeam() {
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

    joinTeam() {
      const formData = new FormData()
      formData.append('team_id', this.teamid)
      axios.post('/api/teams/jointeam', formData)
        .then(response => {
          this.getTeams()
          this.setTeam(response.data.team, 'self')     

          this.stepperCurr = 4
          this.isNewTeam = false
        })
    },

    createTeam() {
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

            this.setTeam(response.data.team, 'self')

            this.stepperCurr = 4
            this.isNewTeam = true
          }
        })
    },

    clearForm() {
      this.teamNotFound = false
      this.teamNotFoundMsg = ''
      this.teamNameError = false
      this.teamNameErrorMsg = ''
      this.teamFound = false
      this.teamTemp = []
      this.teamcontact = []
      this.teamid = ''
      this.stepperCurr = 1
      this.isNewTeam = false
    }
  }
}
</script>
