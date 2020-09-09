<template>
  <v-container fluid>
    <v-row>
      <h1 class="display-1">
        {{ $t('teams.team_settings') }}
      </h1>
    </v-row>

    <v-row>
      <v-tabs v-model="tab" icons-and-text grow class="tab-links mt-10">
        <v-tab href="#tab-general">
          {{ $t('general.general') }}
          <v-icon>mdi-information</v-icon>
        </v-tab>
        <v-tab href="#tab-settings">
          {{ $t('general.settings') }}
          <v-icon>mdi-cog</v-icon>
        </v-tab>
        <v-tab href="#tab-users">
          {{ $t('general.users') }}
          <v-icon>mdi-account-multiple</v-icon>
        </v-tab>


        <v-tabs-items v-model="tab" class="pt-10">

          <!-- TAB: GENERAL -->
          <v-tab-item value="tab-general">
            <v-col cols=6>

              <v-text-field v-model="teamData.name" name="name" :label="$t('teams.team_name')" @input.native="updateTeam($event)"
                  :success="validation.name.success">
                <template v-slot:append v-if="validation.name.success">
                  <v-icon color="green">mdi-check-circle</v-icon>
                </template>
              </v-text-field>

              <v-text-field name="code" :label="$t('teams.team_code')" :value="teamData.code" readonly>
                <template v-slot:append>
                  <v-icon @click="resetCode" color="error">mdi-lock-reset</v-icon>
                </template>
              </v-text-field>

              <v-text-field name="team_date" :label="$t('teams.date_created')" :value="teamData.created_at | formatDate" readonly></v-text-field>


              <v-btn v-b-modal.modal-confirm color="error" @click.prevent="confirmDeleteTeam">
                {{ $t('teams.delete_team') }}
              </v-btn>       

            </v-col>
          </v-tab-item>


          <!-- TAB: SETTINGS -->
          <v-tab-item value="tab-settings">
            <v-col cols=12>
              <v-subheader class="text-subtitle-1 text-uppercase">{{ $t('schedules.shifts') }}</v-subheader>
              <v-divider></v-divider>

              <v-switch v-model="teamData[sw.column]" v-for="sw in settings.shifts.switches" :key="sw.index" :value="teamData[sw.column]" :label="sw.text"
                @change="changeSetting(sw.column, 'bool')" class="pl-5">
              </v-switch>

              <div class="mt-8 mb-8" v-for="num in settings.shifts.numbers" :key="num.index">
                <v-chip class="ml-5 mr-2" color="primary">
                  <v-icon @click="teamData[num.column]-=num.step; changeSetting(num.column, 'num')" small left>mdi-minus</v-icon>
                  <span class="pa-1">{{ formatHoursMinutes(teamData[num.column]) }}</span>
                  <v-icon @click="teamData[num.column]+=num.step; changeSetting(num.column, 'num')" small right>mdi-plus</v-icon>
                </v-chip>
                <v-label>{{ num.text }}</v-label>
              </div>

            </v-col>
          </v-tab-item>


          <!-- TAB: USERS -->
          <v-tab-item value="tab-users">
            <v-data-table :headers="userHeaders" :items="userData">
              <template v-slot:top>
                <v-toolbar flat>
                  <v-toolbar-title>{{ $t('general.users') }}</v-toolbar-title>
                  <v-spacer></v-spacer>

                  <v-dialog v-model="dialog" max-width="500px">
                    <template v-slot:activator="{ on, attrs }">
                      <v-btn color="secondary" class="mb-2" v-bind="attrs" v-on="on">{{ $t('teams.add_user') }}</v-btn>
                    </template>
                    <v-card>
                      <v-card-title>
                        <span class="headline">{{ $t('teams.add_user_to_team') }}</span>
                      </v-card-title>

                      <v-card-text>
                        <v-container>
                          <v-row>
                            <v-col cols="12">
                              <v-text-field v-model="newUserCode" :label="$t('account.user_code')"></v-text-field>
                            </v-col>
                          </v-row>
                        </v-container>
                      </v-card-text>

                      <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="blue darken-1" text @click="close">{{ $t('general.cancel') }}</v-btn>
                        <v-btn color="blue darken-1" text @click="addUser">{{ $t('general.save') }}</v-btn>
                      </v-card-actions>
                    </v-card>
                  </v-dialog>
                </v-toolbar>
              </template>

              <template v-slot:item.created_at="{ item }">
                {{ item.created_at | formatDate }}
              </template>

              <template v-slot:item.actions="{ item }">
                <v-icon small @click="deleteUser(item)">
                  mdi-delete
                </v-icon>
              </template>
            </v-data-table>
          </v-tab-item>
        </v-tabs-items>

      </v-tabs>
    </v-row>


    <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
      {{ snackText }}

      <template v-slot:action="{ attrs }">
        <v-btn v-bind="attrs" text @click="snack = false">{{ $t('general.close') }}</v-btn>
      </template>
    </v-snackbar>

  </v-container>
  
  
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import Form from 'vform'
import helper from '../../mixins/helper'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  data () {
    return {
      dialog: false,
      hasError: false,
      tab: null,
      teamData: [],
      validation: {
        name: {
          success: false, 
          message: null
        }
      },
      form: new Form({
        name: '',
        code: ''
      }),
      userHeaders: [
        { text: this.$t('general.name'), align: 'start', value: 'name' },
        { text: this.$t('general.email'), value: 'email' },
        { text: this.$t('account.user_code'), value: 'user_code' },
        { text: this.$t('account.account_created'), value: 'created_at' },
        { text: this.$t('general.actions'), value: 'actions', sortable: false },
      ],
      settings: {
        shifts: {
          switches: [
            { column: 'setting_shift_request_autoapproval', text: this.$t('teams.setting_shift_request_autoapproval') },
            { column: 'setting_shift_trade_autoapproval', text: this.$t('teams.setting_shift_trade_autoapproval') },
            { column: 'setting_shift_assignment_autoaccept', text: this.$t('teams.setting_shift_assignment_autoaccept') },
          ],
          numbers: [
            { column: 'default_participants_min', text: this.$t('teams.setting_default_participants_min'), step: 1 },
            { column: 'default_participants_max', text: this.$t('teams.setting_default_participants_max'), step: 1 },
            { column: 'default_shift_minutes', text: this.$t('teams.setting_default_shift_minutes'), step: 30 },
          ]
        }
      },
      userData: [],
      newUserCode: '',
      snack: false,
      snackText: '',
      snackColor: ''
    }
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
      team: 'teams/getTeam',
      teams: 'teams/getTeams',
      hasTeam: 'teams/hasTeam'
    })
  },
  
  watch: {
    dialog (val) {
      val || this.close()
    },
  },

  created () {
    this.getTeamData()
    this.getUserData()
  },

  methods: {

    async getUserData () {
      await axios.get('/api/teams/users/' + this.formatJSON(this.team).id)
        .then(response => {
          this.userData = response.data
        })
    },

    getTeamData () {
      axios.get('/api/teams/' + this.formatJSON(this.team).id)
        .then(response => {
          this.teamData = response.data
          this.form = response.data
        })
    },

    updateTeam: _.debounce(async function(e) {
      this.validation[e.target.name].success = true
      setTimeout(() => this.validation[e.target.name].success = false, 3000)

      await axios({
        method: 'patch',      
        url: '/api/teams/' + this.formatJSON(this.team).id,
        data: {
          name: this.teamData.name,
          user_id: this.teamData.user_id
        }
      })

      this.setTeam(this.formatJSON(this.team).id)
      this.$store.dispatch('teams/fetchTeams')
    }, 1000),

    setTeam (teamid) {
      this.$store.dispatch('teams/setTeam', { teamid })
    },

    deleteTeam () {
      axios.delete('/api/teams/' + this.formatJSON(this.team).id)
      this.$store.dispatch('teams/fetchTeams')
      this.$router.push('/home')
    },

    async resetCode () {
      await axios.get('/api/teams/resetcode/' + this.formatJSON(this.team).id)
        .then(response => {
          this.teamData = response.data
        })
    },

    confirmDeleteTeam () {
      this.$bvModal.msgBoxConfirm(this.$t('teams.confirm_delete_text'), {
        title: this.$t('teams.confirm_delete_team'),
        okVariant: 'danger',
        okTitle: this.$t('general.delete'),
        cancelTitle: this.$t('general.cancel'),
        footerClass: 'p-2',
        hideHeaderClose: false,
        centered: true
      })
        .then(value => {
          if (value) {
            this.deleteTeam()
          }
        })
    },

    async deleteUser (user) {
      if (confirm(this.$t('teams.confirm_remove_user'))) {
        await axios({
          method: 'post',      
          url: '/api/teams/leaveteam',
          data: {
            team_id: this.formatJSON(this.team).id,
            user_id: user.id
          }
        })
        .then(response => {
          this.getUserData()

          this.snack = true
          this.snackColor = 'success'
          this.snackText = this.$t('teams.success_remove_user')
        })
      }
    },

    close () {
      this.dialog = false
    },

    async addUser () {
      await axios({
          method: 'post',      
          url: '/api/teams/jointeam',
          data: {
            team_id: this.formatJSON(this.team).id,
            user_code: this.newUserCode
          }
        })
        .then(response => {
          if (!response.data.error) {
            this.getUserData()

            this.snack = true
            this.snackColor = 'success'
            this.snackText = this.$t('teams.success_add_user')
          } else {
            this.snack = true
            this.snackColor = 'error'
            this.snackText = response.data.message
          }
        })

      this.newUserCode = ''
      this.close()
    },


    async changeSetting (setting, valType) {
      var val = ''

      if (valType === 'bool') {
        val = this.teamData[setting] === null ? '0' : '1'
      } else {
        val = this.teamData[setting]
      }

      await axios({
        method: 'post',      
        url: '/api/teams/settings/update',
        data: {
          team_id: this.formatJSON(this.team).id,
          setting: setting,
          value: val
        }
      })

      // Load new settings into store object and cookie
      this.setTeam(this.formatJSON(this.team).id)

    },
  }
}
</script>

<style scoped>
  .tab-links a {
    text-decoration: none;
  }
</style>