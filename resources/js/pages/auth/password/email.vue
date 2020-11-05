<template>
  <v-container fluid>
    <v-card class="mx-auto" max-width="600" outlined>
      <v-card-title>{{ $t('auth.reset_password') }}</v-card-title>
      <v-card-text>
        <v-form >
          <v-text-field v-model="email" name="email" :label="$t('general.email')" 
            :error-messages="emailErrors" @blur="$v.email.$touch()"></v-text-field>


          <v-row>
            <v-col cols=12 class="text-center">
              <!-- Submit Button -->
              <v-btn type="submit" @click.prevent="sendLink" color="primary" block>
                {{ $t('auth.send_password_reset_link') }}
              </v-btn>
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
import { required, email } from 'vuelidate/lib/validators'

export default {
  layout: 'vuetify',
  middleware: 'guest',

  validations: {
    email: { required, email },
  },

  data: () => ({
    email: '',
    snack: false,
    snackText: '',
    snackColor: '',
  }),

  computed: {
    emailErrors () {
      const errors = []
      if (!this.$v.email.$dirty) return errors
      !this.$v.email.email && errors.push(this.$t('auth.email_invalid'))
      !this.$v.email.required && errors.push(this.$t('auth.email_required'))
      return errors
    },
  },

  methods: {
    async sendLink () {
      this.$v.$touch()

      if (!this.$v.$invalid) {
        await axios({
          method: 'post',      
          url: '/api/password/email',
          data: {
            email: this.email,
          }
        })
        .then(response => {
          this.snack = true
          this.snackColor = 'success'
          this.snackText = this.$t('auth.password_reset_link_sent')
        })
      }
    }
  }
}
</script>
