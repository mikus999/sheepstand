<template>
  <v-card hover :width="width">
    <v-card-title class="justify-center text-h6">
      <v-icon class="mr-3">mdi-shield-account</v-icon>
      {{ $t('teams.change_owner') }}
    </v-card-title>

    <v-card-subtitle class="text-center font-weight-bold pt-4">
      {{ this.owner.name }}
    </v-card-subtitle>

    <v-card-text class="my-5">
      <div>{{ $t('teams.select_an_admin') }}</div>

      <v-divider class="mt-2 mb-n2"/>

      <v-radio-group v-model="teamOwner">
        <v-radio 
          v-for="admin in teamAdmins" 
          :key="admin.id" 
          :value="admin.id"
          :label="admin.name"
          >
        </v-radio>
      </v-radio-group>
    </v-card-text>

    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="grey" text v-on:click="$emit('close')">
        {{ $t('general.cancel' ) }}
      </v-btn>
      <v-btn color="primary" text v-on:click="changeTeamOwner()">
        {{ $t('general.save' ) }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'ChangeOwner',
  mixins: [helper],
  props: {
    data: {
      type: [Object, Array]
    },
    owner: {
      type: [Object, Array]
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '170px'
    },
  },

  data() {
    return {
      teamAdmins: null,
      teamOwner: null
    }
  },

  created() {
    this.getTeamAdmins()
  },

  methods: {
    async getTeamAdmins() {
      this.teamOwner = this.team.user_id
      this.teamAdmins = this.data.filter(user => user.team_role == 'team_admin')
    },

    async changeTeamOwner() {
      const newOwner = this.data.filter(user => user.id == this.teamOwner)
      const confirm_msg = this.$t('teams.confirm_change_owner') + '<br /><br />' + newOwner[0].name

      if (await this.$root.$confirm(confirm_msg, null, 'error')) {

        await axios({
            method: 'post',
            url: '/api/teams/settings/update',
            data: {
              team_id: this.team.id,
              setting: 'user_id',
              value: this.teamOwner
            }
          })
          .then(response => {
            this.$store.commit('auth/SET_TEAM', response.data.data.team)
            this.showSnackbar(this.$t('teams.success_change_owner'), 'success')
            this.$emit('close')
          })
      }

    }
  },

}
</script>
