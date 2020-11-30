<template>
  <v-container fluid>
    <v-card class="mx-auto" max-width="500" outlined>
      <v-card-title>{{ $t('auth.reset_password') }}</v-card-title>
      <v-card-text>
        <v-form >
          <v-text-field v-model="email" name="email" :label="$t('general.email')" 
            :error-messages="emailErrors" @blur="$v.email.$touch()" readonly></v-text-field>

          <v-text-field v-model="password" name="password" :label="$t('auth.password')"
            :error-messages="passwordErrors" @blur="$v.password.$touch()"
            :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"
            ></v-text-field>

          <v-text-field v-model="password2" name="password2" :label="$t('auth.confirm_password')"
            :error-messages="passwordErrors2" @blur="$v.password2.$touch()" @input="$v.password2.$touch()"
            :append-icon="showPwd2 ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd2 ? 'text' : 'password'" @click:append="showPwd2 = !showPwd2"
            ></v-text-field>


          <v-row>
            <v-col cols=12 class="text-center">
              <!-- Submit Button -->
              <v-btn type="submit" @click.prevent="reset" color="primary" block>
                {{ $t('auth.reset_password') }}
              </v-btn>
            </v-col>
          </v-row>

        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import { required, email, sameAs, minLength } from 'vuelidate/lib/validators'

export default {
  layout: 'vuetify',
  middleware: 'guest',
  mixins: [helper],

  validations: {
    email: { required, email },
    password: { required, minLength: minLength(6) },
    password2: { required, sameAsPassword: sameAs('password') },
  },

  data: () => ({
    status: '',
    token: '',
    email: '',
    password: '',
    password2: '',
    showPwd: false,
    showPwd2: false,
  }),

  computed: {
    emailErrors () {
      const errors = []
      if (!this.$v.email.$dirty) return errors
      !this.$v.email.email && errors.push(this.$t('auth.email_invalid'))
      !this.$v.email.required && errors.push(this.$t('auth.email_required'))
      return errors
    },
    passwordErrors () {
      const errors = []
      if (!this.$v.password.$dirty) return errors
      !this.$v.password.minLength && errors.push(this.$t('auth.password_length', { length: '6' }))
      !this.$v.password.required && errors.push(this.$t('auth.password_required'))
      return errors
    },
    passwordErrors2 () {
      const errors = []
      if (!this.$v.password2.$dirty) return errors
      !this.$v.password2.sameAsPassword && errors.push(this.$t('auth.password_mismatch'))
      return errors
    },
  },

  created () {
    this.email = this.$route.query.email
    this.token = this.$route.params.token
  },

  methods: {
    async reset () {
      this.$v.$touch()

      if (!this.$v.$invalid) {
        await axios({
            method: 'post',      
            url: '/api/password/reset',
            data: {
              token: this.token,
              email: this.email,
              password: this.password,
              password_confirmation: this.password2
            }
          })
          .then(response => {
            this.showSnackbar(this.$t('auth.success_reset_password'), 'success')
            this.$router.push({ name: 'login' })

          })
          .catch(error => {
            this.showSnackbar(this.$t('general.error_alert_text'), 'error')
          })
      }
    },
  }
}
</script>
