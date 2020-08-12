<template>
  <v-container>
    <v-row>
      <h1 class="display-3">
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
        <h4>{{ $t('users') }}</h4>
      </v-col>
    </v-row>  
  </v-container>
  
  
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import Form from 'vform'

export default {
  middleware: 'auth',
  layout: 'vuetify',

  data () {
    return {
      hasError: false,
      pagetitle: 'Team Settings',
      teamdata: [],
      form: new Form({
        name: '',
        code: ''
      })
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

  created () {
    this.getTeamData()
  },

  methods: {
    formatJSON (data) {
      if (data.name) {
        return JSON.parse(JSON.stringify(data))
      } else {
        return JSON.parse(data)
      }
    },

    getTeamData () {
      axios.get('/api/teams/' + this.formatJSON(this.team).id)
        .then(response => {
          this.form = response.data
        })
    },

    async updateTeam () {
      await axios.patch('/api/teams/' + this.form.id, this.form)

      this.$store.dispatch('teams/fetchTeams')
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
    }
  }
}
</script>
