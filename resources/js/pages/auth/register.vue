<template>
  <v-container fluid>
    <v-card class="mx-auto" max-width="500" outlined>
      <v-card-title>{{ $t('auth.register') }}</v-card-title>
      <v-card-text>

        <v-form>
          <v-text-field v-model="name" name="name" :label="$t('general.name')"
            :error-messages="nameErrors" @blur="$v.name.$touch()"></v-text-field>

          <v-text-field v-model="email" name="email" :label="$t('general.email')" 
            :error-messages="emailErrors" @blur="$v.email.$touch()"></v-text-field>

          <v-text-field v-model="password" name="password" :label="$t('auth.password')"
            :error-messages="passwordErrors" @blur="$v.password.$touch()"
            :append-icon="showPwd ? icons.mdiEye : icons.mdiEyeOff" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"
            ></v-text-field>

          <v-text-field v-model="password2" name="password2" :label="$t('auth.confirm_password')"
            :error-messages="passwordErrors2" @blur="$v.password2.$touch()" @input="$v.password2.$touch()"
            :append-icon="showPwd2 ? icons.mdiEye : icons.mdiEyeOff" :type="showPwd2 ? 'text' : 'password'" @click:append="showPwd2 = !showPwd2"
            ></v-text-field>


          <v-row>
            <v-col cols=12 class="text-center">
              <!-- Submit Button -->
              <v-btn type="submit" @click.prevent="register" color="primary" block>
                {{ $t('auth.register') }}
              </v-btn>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols=12 class="text-center">
              <p class="my-8"><span class="h6">{{ $t('auth.login_with') }}</span></p>

              <login-with-google />
              <login-with-facebook />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import axios from 'axios'
import LoginWithGoogle from '~/components/LoginWithGoogle'
import LoginWithFacebook from '~/components/LoginWithFacebook'
import { required, email, sameAs, minLength } from 'vuelidate/lib/validators'
import helper from '~/mixins/helper'

export default {
  layout: 'vuetify',
  middleware: 'guest',
  mixins: [helper],

  components: {
    LoginWithGoogle,
    LoginWithFacebook
  },

  validations: {
    name: { required },
    email: { required, email },
    password: { required, minLength: minLength(6) },
    password2: { required, sameAsPassword: sameAs('password') },
  },

  data: () => ({
    name: '',
    email: '',
    password: '',
    password2: '',
    showPwd: false,
    showPwd2: false,
    mustVerifyEmail: false
  }),

  computed: {
    nameErrors () {
      const errors = []
      if (!this.$v.name.$dirty) return errors
      !this.$v.name.required && errors.push(this.$t('auth.name_required'))
      return errors
    },
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

  methods: {
    async register () {
      this.$v.$touch()

      if (!this.$v.$invalid) {

        await axios({
            method: 'post',      
            url: '/api/register',
            data: {
              name: this.name,
              email: this.email,
              password: this.password,
              password_confirmation: this.password2
            }
          })
          .then(response => {
            // Must verify email fist.
            if (response.data.data.status) {
              this.mustVerifyEmail = true
            } else {

              this.login(response.data.data.user)
            }
          })
          .catch(error => {
            this.showSnackbar(this.$t('auth.error_registration'), 'error')
          });
      }
    },

    async login (userdata) {
      await axios({
        method: 'post',      
        url: '/api/login',
        data: {
          email: this.email,
          password: this.password
        }
      })
      .then(response => {
        if (response.data.data.token) {
          // Save the token.
          this.$store.dispatch('auth/saveToken', {
            token: response.data.data.token,
            remember: this.remember
          })

          // Redirect home when store is initiated
          this.$store.dispatch('general/init').then(() => {
            this.$router.push({
              name: 'home'
            })
          })

        }
      })
      .catch(error => {
        this.showSnackbar(this.$t('auth.error_login'), 'error')
      });
    }
  }
}
</script>
