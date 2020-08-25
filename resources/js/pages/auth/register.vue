<template>
  <v-container>
    <v-card class="w-75 mx-auto" outlined>
      <v-card-title>{{ $t('register') }}</v-card-title>
      <v-card-text>

        <v-form>
          <v-text-field v-model="name" name="name" label="Name" 
            :error-messages="nameErrors" @blur="$v.name.$touch()"></v-text-field>

          <v-text-field v-model="email" name="email" label="Email" 
            :error-messages="emailErrors" @blur="$v.email.$touch()"></v-text-field>

          <v-text-field v-model="password" name="password" label="Password" 
            :error-messages="passwordErrors" @blur="$v.password.$touch()"
            :append-icon="showPwd ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd ? 'text' : 'password'" @click:append="showPwd = !showPwd"
            ></v-text-field>

          <v-text-field v-model="password2" name="password2" label="Confirm Password" 
            :error-messages="passwordErrors2" @blur="$v.password2.$touch()" @input="$v.password2.$touch()"
            :append-icon="showPwd2 ? 'mdi-eye' : 'mdi-eye-off'" :type="showPwd2 ? 'text' : 'password'" @click:append="showPwd2 = !showPwd2"
            ></v-text-field>


          <v-row>
            <v-col cols=12 class="text-center">
              <!-- Submit Button -->
              <v-btn type="submit" @click.prevent="register" color="secondary">
                {{ $t('register') }}
              </v-btn>
            </v-col>
          </v-row>

          <v-row>
            <v-col cols=12 class="text-center">
              <span class="h6 mr-2">{{ $t('login_with') }}:</span><br>
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
        <v-btn v-bind="attrs" text @click="snack = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script>
import axios from 'axios'
import LoginWithGoogle from '~/components/LoginWithGoogle'
import LoginWithFacebook from '~/components/LoginWithFacebook'
import { required, email, sameAs, minLength } from 'vuelidate/lib/validators'

export default {
  layout: 'vuetify',
  middleware: 'guest',

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
    snack: false,
    snackText: '',
    snackColor: '',
    mustVerifyEmail: false
  }),

  computed: {
    nameErrors () {
      const errors = []
      if (!this.$v.name.$dirty) return errors
      !this.$v.name.required && errors.push('Name is required')
      return errors
    },
    emailErrors () {
      const errors = []
      if (!this.$v.email.$dirty) return errors
      !this.$v.email.email && errors.push('Must be valid e-mail')
      !this.$v.email.required && errors.push('E-mail is required')
      return errors
    },
    passwordErrors () {
      const errors = []
      if (!this.$v.password.$dirty) return errors
      !this.$v.password.minLength && errors.push('Name must be at least 6 characters')
      !this.$v.password.required && errors.push('Password is required')
      return errors
    },
    passwordErrors2 () {
      const errors = []
      if (!this.$v.password2.$dirty) return errors
      !this.$v.password2.sameAsPassword && errors.push('Passwords must match')
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
            if (response.data.status) {
              this.mustVerifyEmail = true
            } else {

              this.login(response.data)
            }
          })
          .catch(error => {
              this.snack = true
              this.snackColor = 'error'
              this.snackText = "Error completing registration."
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
        if (response.data.token) {
          // Save the token.
          this.$store.dispatch('auth/saveToken', {
            token: response.data.token,
            remember: this.remember
          })

          // Update the user.
          this.$store.dispatch('auth/updateUser', { user: userdata })

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
          this.snackText = "Error signing in."
      });
    }
  }
}
</script>
