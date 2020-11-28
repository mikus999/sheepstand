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
              <v-card outlined class="ma-6">
                <v-card-title>
                  <v-icon left>mdi-telegram</v-icon>
                  {{ $t('notifications.notifications')}}
                </v-card-title>

                <v-card-text>
                  <div>
                    <p>
                      <span>{{ $t('general.status') }}: </span>
                      <v-icon v-if="notificationsEnabled" small color="success">mdi-checkbox-marked-circle</v-icon>
                      <v-icon v-else small color="red">mdi-close-circle</v-icon>
                      <span class="font-weight-bold">{{ notificationsEnabled ? $t('general.enabled') : $t('general.disabled') }}</span>
                    </p>
                  </div>

                  <!-- If notifications are setup and working properly -->
                  <div v-if="!chatError && chatInfo">
                    <p><span>{{ $t('notifications.group_name')}}: </span><span class="font-weight-bold">{{ chatInfo.title }}</span></p>
                    <p><span>{{ $t('notifications.group_description')}}: </span><span class="font-weight-bold">{{ chatInfo.description }}</span></p>
                    <p>
                      <span>{{ $t('notifications.invite_link')}}: </span>
                      <span class="font-weight-bold">{{ chatInfo.invite_link }}</span>
                      <v-btn @click="copyText(chatInfo.invite_link)" icon>
                        <v-icon small>mdi-content-copy</v-icon>
                      </v-btn>
                    </p>
                  </div>

                  <!-- If notifications have not been setup -->
                  <div v-else-if="!chatError && !chatInfo">
                    <p><span>{{ $t('notifications.feature_explanation_team') }}</span></p>
                    <v-btn :to="{ name: 'notifications.setup' }" block>{{ $t('notifications.setup_now') }}</v-btn>
                  </div>

                  <!-- If notifications are setup but there was a problem retrieving chat details -->
                  <div v-else-if="chatError">
                    <p><span>{{ $t('general.error')}}: </span><span class="font-weight-bold red--text">{{ $t('notifications.notifications_problem_admin') }}</span></p>
                  </div>
                </v-card-text>
              </v-card>
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
          <v-data-table 
            :headers="userHeaders" 
            :items="userData"
            :search="userSearch"
            sort-by="team_role"
            sort-desc
            >
            <template v-slot:top>
              <v-toolbar flat>
                <v-text-field
                  v-model="userSearch"
                  :label="$t('general.search')"
                  prepend-inner-icon="mdi-magnify"
                  single-line
                  hide-details
                ></v-text-field>
                <v-spacer></v-spacer>
                <v-dialog v-model="dialog" max-width="500px">
                  <template v-slot:activator="{ on, attrs }">
                    <v-btn 
                      color="secondary" 
                      class="mb-2" 
                      :block="$vuetify.breakpoint.xs"
                      >
                      <v-icon left small>mdi-account-plus</v-icon>
                      {{ $t('teams.add_user') }}
                    </v-btn>
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

            <template v-slot:item.team_role="{ item }">
              <a @click="showRolesOverlay(item)" class="text-no-decoration" v-if="team.user_id != item.id">
                <v-icon small>mdi-shield-account</v-icon>
                {{ item.team_role ? $t('roles.' + item.team_role) : $t('roles.not_assigned') }}
              </a>

              <a @click="showChangeOwnerOverlay(item)" class="text-no-decoration" v-else>
                <v-icon small>mdi-shield-account</v-icon>
                {{ $t('teams.owner') }}
              </a>

            </template>

            <template v-slot:item.actions="{ item }">           
              <v-btn icon small @click="removeUser(item)" v-if="team.user_id != item.id">
                <v-icon small>mdi-account-minus</v-icon>
              </v-btn>

            </template>
          </v-data-table>
        </v-tab-item>
      </v-tabs-items>

    </v-tabs>


    <v-overlay :value="rolesOverlay" :dark="theme=='dark'">
      <UserRoles :data="currUser" width="300px" height="100%" @close="rolesOverlay = false; getUserData()"></UserRoles>
    </v-overlay>

    <v-overlay :value="changeOwnerOverlay" :dark="theme=='dark'">
      <ChangeOwner :data="userData" :owner="currUser" width="300px" height="100%" @close="changeOwnerOverlay = false; getUserData()"></ChangeOwner>
    </v-overlay>
  </v-card>
</v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import mtproto from '~/mixins/telegram'
import UserRoles from '~/components/UserRoles.vue'
import ChangeOwner from '~/components/ChangeOwner.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, mtproto],
  components: { 
    UserRoles,
    ChangeOwner
  },

  data() {
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
      userHeaders: [{
          text: this.$t('general.name'),
          align: 'start',
          value: 'name'
        },
        {
          text: this.$t('general.email'),
          value: 'email'
        },
        {
          text: this.$t('account.user_code'),
          value: 'user_code'
        },
        {
          text: this.$t('account.user_role'),
          value: 'team_role'
        },
        {
          text: this.$t('general.actions'),
          value: 'actions',
          sortable: false
        },
      ],
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
      userData: null,
      newUserCode: '',
      chatInfo: null,
      chatError: false,
      userSearch: '',
      rolesOverlay: false,
      changeOwnerOverlay: false,
      currUser: null,
      languages: [],
    }
  },

  watch: {
    dialog(val) {
      val || this.close()
    },
  },

  created() {
    this.getTeamData()
    this.getUserData()
    this.getLanguages()
    this.getNotificationInfo()

  },

  methods: {
    getNotificationInfo() {
      // Initialize the mtproto object
      if (this.notificationsEnabled) {
        this.mtInitialize().then(result => {
          const chat_id = '-100' + this.team.notificationsettings.telegram_channel_id
          // Execute bot api calls
          const url = this.bot_api_base + 'getChat?chat_id=' + chat_id
          axios.get(url)
            .then(response => {
              this.chatInfo = response.data.result
              this.chatError = false
            })
            .catch(error => {
              this.chatInfo = null
              this.chatError = true
            })
        })

        
      } 
    },

    async getUserData() {
      await axios.get('/api/teams/users/' + this.team.id)
        .then(response => {
          this.userData = response.data
        })
    },

    getTeamData() {
      axios.get('/api/teams/' + this.team.id)
        .then(response => {
          this.teamData = response.data
        })
    },


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
              this.setTeam(this.teams[0].id)

              this.$router.push('/home')
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

    async removeUser(user) {
      if (await this.$root.$confirm(this.$t('teams.confirm_remove_user'), null, 'error')) {
        await axios({
            method: 'post',
            url: '/api/teams/leaveteam',
            data: {
              team_id: this.team.id,
              user_id: user.id
            }
          })
          .then(response => {
            this.getUserData()
            this.showSnackbar(this.$t('teams.success_remove_user'), 'success')
          })
      }
    },

    close() {
      this.dialog = false
    },

    showRolesOverlay(user) {
      this.currUser = user
      this.rolesOverlay = true
    },

    showChangeOwnerOverlay(user) {
      this.currUser = user
      this.changeOwnerOverlay = true
    },

    async addUser() {
      await axios({
          method: 'post',
          url: '/api/teams/jointeam',
          data: {
            team_id: this.team.id,
            user_code: this.newUserCode
          }
        })
        .then(response => {
          if (!response.data.error) {
            this.getUserData()
            this.showSnackbar(this.$t('teams.success_add_user'), 'success')
          } else {
            this.showSnackbar(this.$t(response.data.message, 'error'))
          }
        })

      this.newUserCode = ''
      this.close()
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
