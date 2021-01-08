<template>
  <v-container fluid>
    <v-row>
      <v-card width="100%" :flat="$vuetify.breakpoint.xs">
        <v-card-title>
          <v-icon left>{{ mdiAccountTie }}</v-icon>
          {{ $t('account.account_settings')}}
        </v-card-title>

        <v-tabs v-model="tab" icons-and-text grow class="tab-links">
          <v-tab href="#tab-general">
            <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.general') }}</span>
            <v-icon>{{ mdiInformation }}</v-icon>
          </v-tab>
          <v-tab href="#tab-settings">
            <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.settings') }}</span>
            <v-icon>{{ mdiCog }}</v-icon>
          </v-tab>
          <v-tab href="#tab-security">
            <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.security') }}</span>
            <v-icon>{{ mdiSecurity }}</v-icon>
          </v-tab>


          <v-tabs-items v-model="tab" class="pt-10">

            <!-- TAB: GENERAL -->
            <v-tab-item value="tab-general">
              <v-row class="mx-2">
                <v-col cols=12>
                  <v-text-field v-model="userData.name" name="name" :label="$t('general.name')" @input.native="updateUser($event)" 
                      :success="validation.name.success">
                    <template v-slot:append v-if="validation.name.success">
                      <v-icon color="green">{{ mdiCheckCircle }}</v-icon>
                    </template>
                  </v-text-field>
                  <v-text-field v-model="userData.email" name="email" :label="$t('general.email')" @input.native="updateUser($event)" 
                      :success="validation.email.success">
                    <template v-slot:append v-if="validation.email.success">
                      <v-icon color="green">{{ mdiCheckCircle }}</v-icon>
                    </template>
                  </v-text-field>

                  <v-select 
                    v-model="mate" 
                    :items="teamUsers" 
                    item-text="name" 
                    item-value="id" 
                    return-object 
                    @change="updateMarriageMate"
                    :label="$t('account.marriage_mate')"
                    clearable
                  />


                  <v-select 
                    v-model="userData.fts_status" 
                    :items="ftsStatus"
                    item-text="text"
                    item-value="value"
                    @change="updateStatusField('fts')"
                    :label="$t('account.fts_status')"
                  />

                  <v-switch
                    v-model="userData.driver"
                    @change="updateStatusField('driver')"
                    :label="$t('account.has_auto')"
                  />


                  <div class="my-6" v-if="hasTeam">
                    <v-btn color="error" @click.prevent="leaveTeam" :disabled="isTeamOwner">
                      {{ $t('teams.leave_team') }}
                    </v-btn>
                  </div>
                </v-col>
              </v-row>

              <v-divider class="my-12" v-if="$vuetify.breakpoint.xs"></v-divider>

              <v-row>
                <v-col cols=12 md=7 lg=6 class="pa-sm-6">
                  <AvailabilitySchedule :data="user" :key="availability_key" v-on:updated="availability_key++" />
                </v-col>

                <v-col cols=12 md=5 lg=6 class="pa-sm-6">
                  <VacationSchedule :data="user" />
                </v-col>
              </v-row>
            </v-tab-item>


            <!-- TAB: SETTINGS -->
            <v-tab-item value="tab-settings">
              <v-col cols=12>
                <v-row>
                  <div class="mx-auto">
                    <v-subheader class="text-subtitle-1 text-uppercase">{{ $t('account.appearance') }}</v-subheader>
                  </div>
                </v-row>
                <v-row>
                  <div :class="$vuetify.breakpoint.xs ? 'mx-auto' : 'ml-sm-auto'">
                    <v-hover v-slot="{ hover }">
                      <v-skeleton-loader
                        type="article, actions"
                        :width="$vuetify.breakpoint.smAndDown ? 250 : 300"
                        :class="'ma-8 ' + (!$vuetify.theme.dark ? 'border-blue' : '')"
                        :elevation="hover ? 15 : 5"
                        boilerplate
                        tile
                        light
                        @click="changeTheme('light')"
                      ></v-skeleton-loader>
                    </v-hover>
                    <p class="text-center text-subtitle-1 font-weight-light">{{ $t('account.light_mode') }}</p>
                  </div>

                  <div :class="$vuetify.breakpoint.xs ? 'mx-auto' : 'mr-sm-auto'">
                    <v-hover v-slot="{ hover }">
                      <v-skeleton-loader
                        type="article, actions"
                        :width="$vuetify.breakpoint.smAndDown ? 250 : 300"
                        :class="'ma-8 ' + ($vuetify.theme.dark ? 'border-blue' : '')"
                        :elevation="hover ? 15 : 5"
                        boilerplate
                        tile
                        dark
                        @click="changeTheme('dark')"
                      ></v-skeleton-loader>
                    </v-hover>
                    <p class="text-center text-subtitle-1 font-weight-light">{{ $t('account.dark_mode') }}</p>
                  </div>
                </v-row>

                <v-divider class="my-12"></v-divider>

                <v-row>
                  <div class="mx-auto">
                    <v-subheader class="text-subtitle-1 text-uppercase">{{ $t('account.preferences') }}</v-subheader>
                  </div>
                </v-row>
              </v-col>
            </v-tab-item>


            <!-- TAB: SECURITY -->
            <v-tab-item value="tab-security">
              <v-col md=8 offset-md=2>
                <v-text-field v-model="password1" name="password1" :label="$t('auth.new_password')" 
                  :error-messages="passwordErrors" @blur="$v.password1.$touch()"
                  :append-icon="showPwd ? mdiEye : mdiEyeOff" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"
                  ></v-text-field>

                <v-text-field v-model="password2" name="password2" :label="$t('auth.confirm_password')" 
                  :error-messages="passwordErrors2" @blur="$v.password2.$touch()" @input="$v.password2.$touch()"
                  :append-icon="showPwd2 ? mdiEye : mdiEyeOff" :type="showPwd2 ? 'text' : 'password'" @click:append="showPwd2 = !showPwd2"
                  ></v-text-field>
                
                <v-btn type="submit" @click.prevent="updatePassword" color="primary">
                  {{ $t('auth.reset_password') }}
                </v-btn>
              </v-col>
            </v-tab-item>
          </v-tabs-items>

        </v-tabs>
      </v-card>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '../../mixins/helper'
import { required, email, sameAs, minLength } from 'vuelidate/lib/validators'
import debounce from 'lodash/debounce'
import AvailabilitySchedule from '~/components/AvailabilitySchedule.vue'
import VacationSchedule from '~/components/VacationSchedule.vue'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  components: {
    AvailabilitySchedule,
    VacationSchedule,
  },

  validations: {
    password1: { required, minLength: minLength(6) },
    password2: { required, sameAsPassword: sameAs('password1') },
  },

  data () {
    return {
      tab: null,
      validation: {
        name: {
          success: false, 
          message: null
        },
        email: {
          success: false, 
          message: null 
        }
      },
      settings: {
        shifts: {
          switches: [
            { column: 'setting_shift_request_autoapproval', text: this.$t('teams.setting_shift_request_autoapproval') },
            { column: 'setting_shift_assignment_autoaccept', text: this.$t('teams.setting_shift_assignment_autoaccept') },
            { column: 'setting_shift_trade_autoapproval', text: this.$t('teams.setting_shift_trade_autoapproval') },
          ],
          numbers: [
            { column: 'default_participants_min', text: this.$t('teams.setting_default_participants_min'), step: 1 },
            { column: 'default_participants_max', text: this.$t('teams.setting_default_participants_max'), step: 1 },
            { column: 'default_shift_minutes', text: this.$t('teams.setting_default_shift_minutes'), step: 30 },
          ]
        }
      },
      userData: [],
      teamUsers: null,
      mate: null,
      password1: null,
      password2: null,
      showPwd: false,
      showPwd2: false,
      availability_key: 0,
    }
  },

  computed: {
    passwordErrors () {
      const errors = []
      if (!this.$v.password1.$dirty) return errors
      !this.$v.password1.minLength && errors.push(this.$t('auth.password_length', { length: '6' }))
      !this.$v.password1.required && errors.push(this.$t('auth.password_required'))
      return errors
    },
    passwordErrors2 () {
      const errors = []
      if (!this.$v.password2.$dirty) return errors
      !this.$v.password2.sameAsPassword && errors.push(this.$t('auth.password_mismatch'))
      return errors
    },
  },
  
  watch: {

  },

  created () {
    this.getUserData()
    this.getTeamUsers()
  },

  methods: {

    async getUserData () {
      this.userData = this.user
      this.mate = this.user.marriage_mate
    },

            
    async getTeamUsers() {
      await axios.get('/api/teams/' + this.team.id + '/users/')
        .then(response => {
          this.teamUsers = response.data.data.users.filter(u => 
            u.id != this.user.id
          )
        })
    },

    updateUser: debounce(async function(e) {
      this.validation[e.target.name].success = true
      setTimeout(() => this.validation[e.target.name].success = false, 3000)

      await axios({
        method: 'patch',      
        url: '/api/account/profile',
        data: this.userData
      })
    }, 1000),

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
          team_id: this.team.id,
          setting: setting,
          value: val
        }
      })

    },

    async updatePassword () {
      this.$v.$touch()

      if (!this.$v.$invalid) {
        await axios({
          method: 'patch',      
          url: '/api/account/password',
          data: {
            user: this.user,
            password: this.password1
          }
        })
        .then(response => {
            this.showSnackbar(this.$t('auth.success_reset_password'), 'success')
        });
      }
    },


    async updateStatusField (field) {
      var url = null
      var status = null

      if (field == 'fts') {
        url = '/api/account/fts'
        status = this.userData.fts_status
      } else if (field == 'driver') {
        url = '/api/account/driver'
        status = this.userData.driver
      }

      await axios({
        method: 'post',      
        url: url,
        data: {
          team_id: this.team.id,
          status: status
        }
      })
      .then(response => {
          this.refreshUser()
          this.showSnackbar(this.$t('general.info_updated'), 'success')
      });
    },


    async updateMarriageMate () {
      if (await this.$root.$confirm(this.$t('account.confirm_change_mate'), null, 'error')) {
        await axios({
          method: 'post',      
          url: '/api/account/marriage',
          data: {
            team_id: this.team.id,
            mate1_id: this.user.id,
            mate2_id: this.mate == undefined ? null : this.mate.id
          }
        })
        .then(response => {
            this.refreshUser()
            this.showSnackbar(this.$t('general.info_updated'), 'success')
        });
      } else {
        this.mate = this.user.marriage_mate
      }
    },

    async leaveTeam() {
      if (await this.$root.$confirm(this.$t('teams.confirm_leave_team'), null, 'error')) {
        await axios({
            method: 'post',
            url: '/api/teams/leaveteam',
            data: {
              team_id: this.team.id,
            }
          })
          .then(response => {
            this.showSnackbar(this.$t('teams.success_leave_team'), 'success')
            this.getTeams()
          })
      }
    },
  }
}
</script>

<style scoped>
  .tab-links a {
    text-decoration: none;
  }

  .border-blue {
    border: 3px solid #0288D1;
  }
</style>