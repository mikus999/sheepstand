<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        Team Settings
      </h1>
    </v-row>

    <v-row>
      <v-col md="12">
        <form @submit.prevent="updateTeam">
          <alert-success :form="form" :message="$t('info_updated')" />

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-md-right">{{ $t('name') }}</label>
            <div class="col-md-7">
              <input v-model="form.name" class="form-control" type="text" name="name">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-md-right">{{ $t('team_code') }}</label>
            <div class="col-md-7 col-form-label">
              {{ form.code }}
              <a href="#" class="ml-4 inline-link-sm" @click.prevent="resetCode">{{ $t('reset_code') }}</a>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-md-right">{{ $t('date_created') }}</label>
            <div class="col-md-7 col-form-label">
              {{ form.created_at | formatDate }}
            </div>
          </div>

          <!-- Submit Button -->
          <v-row>
            <v-col md="7" offset-md="2">
              <v-btn color="secondary" type="submit">
                {{ $t('update') }}
              </v-btn>
              <v-btn v-b-modal.modal-confirm color="error" @click.prevent="confirmDeleteTeam">
                {{ $t('delete_team') }}
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
              <v-toolbar-title>{{ $t('users') }}</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-dialog v-model="dialog" max-width="500px">
                <template v-slot:activator="{ on, attrs }">
                  <v-btn color="secondary" class="mb-2" v-bind="attrs" v-on="on">Add User</v-btn>
                </template>
                <v-card>
                  <v-card-title>
                    <span class="headline">Add User to Team</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-row>
                        <v-col cols="12" sm="6" md="4">
                          <v-text-field v-model="newUserCode" label="User Code"></v-text-field>
                        </v-col>
                      </v-row>
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" text @click="close">Cancel</v-btn>
                    <v-btn color="blue darken-1" text @click="save">Save</v-btn>
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
            <v-btn v-bind="attrs" text @click="snack = false">Close</v-btn>
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
      pagetitle: 'Team Settings',
      teamdata: [],
      form: new Form({
        name: '',
        code: ''
      }),
      userHeaders: [
        { text: 'Name', align: 'start', value: 'name' },
        { text: 'Email', value: 'email' },
        { text: 'User Code', value: 'user_code' },
        { text: 'Account Created', value: 'created_at' },
        { text: 'Actions', value: 'actions', sortable: false },
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
      this.$bvModal.msgBoxConfirm(this.$t('confirm_delete_text'), {
        title: this.$t('confirm_delete_team'),
        okVariant: 'danger',
        okTitle: this.$t('delete'),
        cancelTitle: this.$t('cancel'),
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
      if (confirm('Are you sure you want to remove this user from the team?')) {
        const formData = new FormData()
        formData.append('user_id', user.id)
        formData.append('team_id', this.formatJSON(this.team).id)
        axios.post('/api/teams/leaveteam', formData)
          .then(response => {
            this.userData.splice(index, 1)  
            this.snack = true
            this.snackColor = 'success'
            this.snackText = response.data.message
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

      this.close()
    }
  }
}
</script>
