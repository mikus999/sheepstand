<template>
<v-container fluid>
  <v-row>
    <PageTitle :title="$t('teams.team_settings')"></PageTitle>
  </v-row>

  <v-card width="100%">
    <v-tabs v-model="tab" icons-and-text grow class="tab-links mt-10">
      <v-tab href="#tab-general">
        <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.general') }}</span>
        <v-icon>mdi-information</v-icon>
      </v-tab>
      <v-tab href="#tab-settings">
        <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.settings') }}</span>
        <v-icon>mdi-cog</v-icon>
      </v-tab>
      <v-tab href="#tab-users">
        <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.users') }}</span>
        <v-icon>mdi-account-multiple</v-icon>
      </v-tab>

      <v-tabs-items v-model="tab" class="pt-10">

        <!-- TAB: GENERAL -->
        <v-tab-item value="tab-general">
          <v-row class="mx-2">
            <v-col cols=12 sm=6>

              <v-text-field v-model="teamData.display_name" name="display_name" :label="$t('teams.team_name')" @input.native="updateTeam($event)" :success="validation.name.success">
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

              <v-select v-model="teamData.language" :items="languages" item-text="native_name" item-value="code" :label="$t('teams.default_language')" :hint="$t('teams.default_language_hint')" persistent-hint
                @change="changeSetting('language', 'str')" outlined class="mt-4"></v-select>

              <div class="my-6">
                <v-btn color="error" @click.prevent="deleteTeam">
                  {{ $t('teams.delete_team') }}
                </v-btn>
              </div>
            </v-col>

            <v-col cols=12 sm=6>
              <NotificationInfo v-on:updated="notification_key++" :key="notification_key" />
            </v-col>
          </v-row>
        </v-tab-item>

        <!-- TAB: SETTINGS -->
        <v-tab-item value="tab-settings">
          <v-col cols=12>
            <v-row>
              <div class="mx-auto">
                <v-subheader class="text-subtitle-1 text-uppercase">{{ $t('schedules.shifts') }}</v-subheader>
              </div>
            </v-row>
            
            <v-divider></v-divider>

            <v-row>
              <div class="mx-auto">
                <v-switch v-model="teamData[sw.column]" v-for="sw in settings.shifts.switches" :key="sw.index" 
                  :value="teamData[sw.column]" :label="sw.text" @change="changeSetting(sw.column, 'bool')" class="pl-5">
                </v-switch>

                <div class="mt-8 mb-8" v-for="num in settings.shifts.numbers" :key="num.index">
                  <v-input class="ml-5">
                    <v-chip color="primary" class="mr-3">
                      <v-icon @click="teamData[num.column]-=num.step; changeSetting(num.column, 'num')" small left>mdi-minus</v-icon>
                      <span class="pa-1">{{ formatHoursMinutes(teamData[num.column]) }}</span>
                      <v-icon @click="teamData[num.column]+=num.step; changeSetting(num.column, 'num')" small right>mdi-plus</v-icon>
                    </v-chip>
                    <v-label class="float-right">{{ num.text }}</v-label>
                  </v-input>
                </div>
              </div>
            </v-row>

          </v-col>
        </v-tab-item>

        <!-- TAB: USERS -->
        <v-tab-item value="tab-users">
          <UserTable team-users />
        </v-tab-item>
      </v-tabs-items>

    </v-tabs>

  </v-card>
</v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import mtproto from '~/mixins/telegram'
import UserTable from '~/components/UserTable.vue'
import NotificationInfo from '~/components/NotificationInfo.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, mtproto],
  components: { 
    UserTable,
    NotificationInfo
  },

  data() {
    return {
      hasError: false,
      tab: null,
      teamData: [],
      validation: {
        name: {
          success: false,
          message: null
        }
      },
      settings: {
        shifts: {
          switches: [{
              column: 'setting_shift_request_autoapproval',
              text: this.$t('teams.setting_shift_request_autoapproval')
            },
            {
              column: 'setting_shift_trade_autoapproval',
              text: this.$t('teams.setting_shift_trade_autoapproval')
            },
            {
              column: 'setting_shift_assignment_autoaccept',
              text: this.$t('teams.setting_shift_assignment_autoaccept')
            },
          ],
          numbers: [{
              column: 'default_participants_min',
              text: this.$t('teams.setting_default_participants_min'),
              step: 1
            },
            {
              column: 'default_participants_max',
              text: this.$t('teams.setting_default_participants_max'),
              step: 1
            },
            {
              column: 'default_shift_minutes',
              text: this.$t('teams.setting_default_shift_minutes'),
              step: 30
            },
          ]
        }
      },
      languages: [],
      notification_key: 0
    }
  },


  created() {
    this.refreshTeam()
    this.getLanguages()

    this.teamData = this.team
  },

  methods: {
  
    async getLanguages() {
      await axios({
        method: 'get',      
        url: '/api/translation/languages/site',
      })
      .then(response => {
        this.languages = response.data
      })
    },


    updateTeam: _.debounce(async function (e) {
      this.validation[e.target.name].success = true
      setTimeout(() => this.validation[e.target.name].success = false, 3000)

      await axios({
        method: 'patch',
        url: '/api/teams/' + this.team.id,
        data: {
          name: this.teamData.display_name,
          user_id: this.teamData.user_id
        }
      })

      this.getTeams()
    }, 1000),

    async deleteTeam() {
      if (await this.$root.$confirm(this.$t('teams.confirm_delete_text'), null, 'error')) {
        await axios.delete('/api/teams/' + this.team.id)
          .then(response => {
            this.getTeams()

            if (this.teams.length == 0) {
              this.$router.push({
                name: 'teams.join'
              })
            } else {
              this.setTeam(this.teams[0].id, 'home')
            }
          })
      }
    },

    async resetCode() {
      await axios.get('/api/teams/resetcode/' + this.team.id)
        .then(response => {
          this.teamData = response.data
        })
    },


    async changeSetting(setting, valType) {
      var val = ''

      if (valType === 'bool') {
        val = this.teamData[setting] === null ? 0 : 1
      } else {
        val = this.teamData[setting]
      }
      

      await axios({
          method: 'post',
          url: '/api/teams/settings/update',
          data: {
            team_id: this.team.id,
            setting: setting,
            value: val
          }
        })
        .then(response => {
          this.teamData = response.data
          this.refreshStore()
        })
    },

  }
}
</script>

<style scoped>
.tab-links a {
  text-decoration: none;
}
</style>
