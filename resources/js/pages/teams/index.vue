<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        {{ $t('teams.team_settings') }}
      </h1>
    </v-row>

    <v-row>
      <v-col md="12">
        <form @submit.prevent="updateTeam">
          <alert-success :form="form" :message="$t('general.info_updated')" />

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-md-right">{{ $t('general.name') }}</label>
            <div class="col-md-7">
              <input v-model="form.name" class="form-control" type="text" name="name">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-md-right">{{ $t('teams.team_code') }}</label>
            <div class="col-md-7 col-form-label">
              {{ form.code }}
              <a href="#" class="ml-4 inline-link-sm" @click.prevent="resetCode">{{ $t('teams.reset_code') }}</a>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-md-right">{{ $t('teams.date_created') }}</label>
            <div class="col-md-7 col-form-label">
              {{ form.created_at | formatDate }}
            </div>
          </div>

          <!-- Submit Button -->
          <v-row>
            <v-col md="7" offset-md="2">
              <v-btn color="secondary" type="submit">
                {{ $t('general.update') }}
              </v-btn>
              <v-btn v-b-modal.modal-confirm color="error" @click.prevent="confirmDeleteTeam">
                {{ $t('teams.delete_team') }}
              </v-btn>       
            </v-col>
          </v-row>
          
        </form>
      </v-col>
    </v-row>

    <v-row>
      <v-col md="12">
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
                    <v-btn color="blue darken-1" text @click="save">{{ $t('general.save') }}</v-btn>
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

        <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
          {{ snackText }}

          <template v-slot:action="{ attrs }">
            <v-btn v-bind="attrs" text @click="snack = false">{{ $t('general.close') }}</v-btn>
          </template>
        </v-snackbar>
      </v-col>
    </v-row>  
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
      teamdata: [],
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
          this.form = response.data
        })
    },

    async updateTeam () {
      await axios.patch('/api/teams/' + this.form.id, this.form)

      this.setTeam(this.form.id)
      this.$store.dispatch('teams/fetchTeams')
    },

    setTeam (teamid) {
      this.$store.dispatch('teams/setTeam', { teamid })
    },

    deleteTeam () {
      axios.delete('/api/teams/' + this.form.id)
      this.$store.dispatch('teams/fetchTeams')
      this.$router.push('/home')
    },

    async resetCode () {
      await axios.get('/api/teams/resetcode/' + this.form.id)
        .then(response => {
          this.form = response.data
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

    deleteUser (user) {
      const index = this.userData.indexOf(user.id)
      if (confirm(this.$t('teams.confirm_remove_user'))) {
        const formData = new FormData()
        formData.append('user_id', user.id)
        formData.append('team_id', this.formatJSON(this.team).id)
        axios.post('/api/teams/leaveteam', formData)
          .then(response => {
            this.userData.splice(index, 1)  
            this.snack = true
            this.snackColor = 'success'
            this.snackText = this.$t('teams.success_remove_user')
          })

      }
    },

    close () {
      this.dialog = false
    },

    save () {
      const formData = new FormData()
      formData.append('user_code', this.newUserCode)
      formData.append('team_id', this.formatJSON(this.team).id)
      axios.post('/api/teams/jointeam', formData)
        .then(response => {
          if (!response.data.error) {
            this.userData.push(response.data.user)
          } else {
            this.snack = true
            this.snackColor = 'error'
            this.snackText = response.data.message
          }
        })

      this.newUserCode = ''
      this.close()
    }
  }
}
</script>
