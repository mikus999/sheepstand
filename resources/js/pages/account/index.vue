<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('account.account_settings')"></PageTitle>
    </v-row>

    <v-row>
      <v-tabs v-model="tab" icons-and-text grow class="tab-links mt-10">
        <v-tab href="#tab-general">
          <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.general') }}</span>
          <v-icon>mdi-information</v-icon>
        </v-tab>
        <v-tab href="#tab-settings">
          <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.settings') }}</span>
          <v-icon>mdi-cog</v-icon>
        </v-tab>
        <v-tab href="#tab-security">
          <span v-show="$vuetify.breakpoint.smAndUp">{{ $t('general.security') }}</span>
          <v-icon>mdi-security</v-icon>
        </v-tab>


        <v-tabs-items v-model="tab" class="pt-10">

          <!-- TAB: GENERAL -->
          <v-tab-item value="tab-general">
            <v-col md=8>
              <v-text-field v-model="userData.name" name="name" :label="$t('general.name')" @input.native="updateUser($event)" 
                  :success="validation.name.success">
                <template v-slot:append v-if="validation.name.success">
                  <v-icon color="green">mdi-check-circle</v-icon>
                </template>
              </v-text-field>
              <v-text-field v-model="userData.email" name="email" :label="$t('general.email')" @input.native="updateUser($event)" 
                  :success="validation.email.success">
                <template v-slot:append v-if="validation.email.success">
                  <v-icon color="green">mdi-check-circle</v-icon>
                </template>
              </v-text-field>
            </v-col>
          </v-tab-item>


          <!-- TAB: SETTINGS -->
          <v-tab-item value="tab-settings">
            <v-col cols=12>
            </v-col>
          </v-tab-item>


          <!-- TAB: SECURITY -->
          <v-tab-item value="tab-security">
            <v-col md=8 offset-md=2>
              <v-text-field v-model="password1" name="password1" :label="$t('auth.new_password')" 
                :error-messages="passwordErrors" @blur="$v.password1.$touch()"
                :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"
                ></v-text-field>

              <v-text-field v-model="password2" name="password2" :label="$t('auth.confirm_password')" 
                :error-messages="passwordErrors2" @blur="$v.password2.$touch()" @input="$v.password2.$touch()"
                :append-icon="showPwd2 ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd2 ? 'text' : 'password'" @click:append="showPwd2 = !showPwd2"
                ></v-text-field>
              
              <v-btn type="submit" @click.prevent="updatePassword" color="primary">
                {{ $t('auth.reset_password') }}
              </v-btn>
            </v-col>
          </v-tab-item>
        </v-tabs-items>

      </v-tabs>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '../../mixins/helper'
import { required, email, sameAs, minLength } from 'vuelidate/lib/validators'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

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
      password1: null,
      password2: null,
      showPwd: false,
      showPwd2: false,
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
  },

  methods: {

    async getUserData () {
      this.userData = this.user
    },

    updateUser: _.debounce(async function(e) {
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
            this.showSnackbar(this.$t('uth.success_reset_password'), 'success')
        });
      }
    }
  }
}
</script>

<style scoped>
  .tab-links a {
    text-decoration: none;
  }
</style>