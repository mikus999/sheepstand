<template>
  <v-container fluid>
    <v-card class="mx-auto" max-width="600" outlined>
      <v-card-title>{{ $t('auth.login') }}</v-card-title>
      <v-card-text>
        <v-form >
          <v-text-field v-model="email" name="email" :label="$t('general.email')" 
            :error-messages="emailErrors" @blur="$v.email.$touch()"></v-text-field>

          <v-text-field v-model="password" password="password" :label="$t('auth.password')" 
            :error-messages="passwordErrors" @blur="$v.password.$touch()"
            :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"
            ></v-text-field>
          
          <v-checkbox v-model="remember" name="remember" :label="$t('auth.remember_me')"></v-checkbox>

          <router-link :to="{ name: 'password.request' }" class="small ml-auto my-auto">
            {{ $t('auth.forgot_password') }}
          </router-link>

          <v-row>
            <v-col cols=12 class="text-center">
              <!-- Submit Button -->
              <v-btn type="submit" @click.prevent="login" color="secondary">
                {{ $t('auth.login') }}
              </v-btn>

              <v-btn :to="{ name: 'register' }" class="ml-2" style="text-decoration: none;">
                {{ $t('auth.register') }}
              </v-btn>
            </v-col>
          </v-row>
          <v-row>
            <v-col cols=12 class="text-center">
              <span class="h6 mr-2">{{ $t('auth.login_with') }}:</span><br>
              <login-with-google />
              <login-with-facebook />
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>

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
import LoginWithGithub from '~/components/LoginWithGithub'
import LoginWithGoogle from '~/components/LoginWithGoogle'
import LoginWithFacebook from '~/components/LoginWithFacebook'
import { required, email } from 'vuelidate/lib/validators'


export default {
  layout: 'vuetify',
  middleware: 'guest',

  components: {
    LoginWithGithub,
    LoginWithGoogle,
    LoginWithFacebook
  },

  validations: {
    email: { required, email },
    password: { required },
  },

  data: () => ({
    email: '',
    password: '',
    remember: null,
    showPwd: false,
    snack: false,
    snackText: '',
    snackColor: ''
  }),

  computed: {
    passwordErrors () {
      const errors = []
      if (!this.$v.password.$dirty) return errors
      !this.$v.password.required && errors.push(this.$t('auth.password_required'))
      return errors
    },
    emailErrors () {
      const errors = []
      if (!this.$v.email.$dirty) return errors
      !this.$v.email.email && errors.push(this.$t('auth.email_invalid'))
      !this.$v.email.required && errors.push(this.$t('auth.email_required'))
      return errors
    },
  },

  methods: {
    async login () {
      this.$v.$touch()

      if (!this.$v.$invalid) {
        await axios({
          method: 'post',      
          url: '/api/login',
          data: {
            email: this.email,
            password: this.password
          }
        })
        .then(response => {
          if (response.data.token) {
            // Save the token.
            this.$store.dispatch('auth/saveToken', {
              token: response.data.token,
              remember: this.remember
            })

            // Fetch the user.
            this.$store.dispatch('auth/fetchUser')

            // Fetch the teams.
            this.$store.dispatch('teams/fetchTeams');

            // Redirect home.
            this.$router.push({ name: 'home' })

          }
        })
        .catch(error => {
            this.snack = true
            this.snackColor = 'error'
            this.snackText = this.$t('auth.credentials_incorrect')
        });
      }
    }
  }
}
</script>
