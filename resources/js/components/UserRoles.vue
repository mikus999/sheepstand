<template>
  <v-card hover :width="width">
    <v-card-title class="justify-center text-h6">
      <v-icon class="mr-3">mdi-shield-account</v-icon>
      {{ $t('account.user_security') }}
    </v-card-title>

    <v-card-subtitle class="text-center font-weight-bold pt-4">
      {{ this.data.name }}
    </v-card-subtitle>

    <!-- Team user security -->
    <v-card-text class="my-5" v-if="!adminRoles">
      <div>{{ $t('account.user_role') }}</div>
      <v-divider class="mt-2 mb-n2"/>
      <v-radio-group v-model="teamRole">
        <v-radio 
          v-for="sr in siteRoles.filter(role => role.global == false)" 
          :key="sr.id" 
          :value="sr.name"
          :label="$t('roles.' + sr.name)"
          @change="changeUserRole(sr.name)"
          >
        </v-radio>
      </v-radio-group>
    </v-card-text>

    <!-- Site user security -->
    <v-card-text class="my-5" v-else>
      <div>{{ $t('account.site_roles') }}</div>
      <v-divider class="mt-2 mb-n2"/>

      <v-checkbox
        v-for="sr in siteRoles.filter(role => role.global == true)"
        v-model="globalRoles"
        :key="sr.id"
        :value="sr.name"
        :label="$t('roles.' + sr.name)"
        @change="changeUserRole(sr.name)"
        hide-details
        >
      </v-checkbox>
    </v-card-text>


    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="primary" text v-on:click="$emit('close')">
        {{ $t('general.close' ) }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'UserRoles',
  mixins: [helper],
  props: {
    data: {
      type: Object
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '170px'
    },
    adminRoles: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      userRoles: null,
      teamRole: null,
      globalRoles: null
    }
  },

  created() {
    this.getUserRoles()
  },

  methods: {
    async getUserRoles() {
      await axios({
        method: 'post',
        url: '/api/user/roles/get',
        data: {
          user_id: this.data.id
        }
      })
      .then(response => {
        this.userRoles = response.data.roles
        this.teamRole = this.userRoles[this.team.id] ? this.userRoles[this.team.id][0] : null
        this.globalRoles = this.userRoles.global
      })
    },

    async changeUserRole(role) {

      if (!this.adminRoles) {
        var changetype = 'sync'
      } else if (this.globalRoles.indexOf(role) >= 0) {
        var changetype = 'add'
      } else {
        var changetype = 'remove'
      }


      await axios({
        method: 'post',
        url: '/api/user/roles/set',
        data: {
          user_id: this.data.id,
          role: role,
          changetype: changetype,
          team_id: this.adminRoles ? null : this.team.id
        }
      })
      .then(response => {
        this.showSnackbar(this.$t('general.info_updated'), 'success')
        this.userRoles = response.data.roles
        this.teamRole = this.userRoles[this.team.id] ? this.userRoles[this.team.id][0] : null
        this.globalRoles = this.userRoles.global
      })
      
    },

  }
}
</script>
