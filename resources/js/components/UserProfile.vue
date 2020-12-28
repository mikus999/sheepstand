<template>
  <v-card hover :width="width">
    <v-card-title class="justify-center text-h6">
      <v-icon class="mr-3">mdi-account-cog</v-icon>
      {{ $t('account.user_profile') }}
    </v-card-title>

    <v-card-subtitle class="text-center font-weight-bold pt-4">
      {{ this.data.name }}
    </v-card-subtitle>

    <v-card-text class="my-5">
      <!-- Team user security -->
      <v-select 
        v-if="!adminRoles"
        v-model="teamRole" 
        :items="siteRoles.filter(role => role.global == false)" 
        item-value="name" 
        @change="changeUserRole"
        :label="$t('account.user_role')"
      >
        <template v-slot:item="{ item }">
          {{ $t('roles.' + item.name) }}
        </template>

        <template v-slot:selection="{ item }">
          {{ $t('roles.' + item.name) }}
        </template>
      </v-select>


      <!-- Site user security -->
      <v-select 
        v-if="adminRoles"
        :value="globalRoles" 
        :items="siteRoles.filter(role => role.global == true)" 
        item-value="name" 
        @input="changeUserRole"
        :label="$t('account.site_roles')"
        multiple
      >
        <template v-slot:item="{ item }">
          {{ $t('roles.' + item.name) }}
        </template>

        <template v-slot:selection="{ item }">
          <v-chip small>{{ $t('roles.' + item.name) }}</v-chip>
        </template>
      </v-select>


      <v-select 
        v-model="userData.fts_status" 
        :items="ftsStatus"
        item-text="text"
        item-value="value"
        @change="updateFTS()"
        :label="$t('account.fts_status')"
      />

      <v-select 
        v-model="mate" 
        :items="teamUsers" 
        item-text="name" 
        item-value="id" 
        return-object 
        @change="updateMarriageMate"
        :label="$t('account.marriage_mate')"
        clearable
      />

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
  name: 'UserProfile',
  mixins: [helper],
  props: {
    data: {
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
    adminRoles: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      userData: [],
      teamUsers: [],
      userRoles: null,
      teamRole: null,
      globalRoles: null,
      mate: null
    }
  },

  created() {
    this.userData = this.data
    this.mate = this.userData.marriage_mate
    this.getTeamUsers()
    this.getUserRoles()
  },

  methods: {           
    async getTeamUsers() {
      await axios.get('/api/teams/' + this.team.id + '/users/')
        .then(response => {
          this.teamUsers = response.data.filter(u => 
            u.id != this.userData.id
          )
        })
    },


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



    async updateFTS () {
      await axios({
        method: 'post',      
        url: '/api/account/fts',
        data: {
          user_id: this.userData.id,
          status: this.userData.fts_status
        }
      })
      .then(response => {
        this.showSnackbar(this.$t('general.info_updated'), 'success')

        if (this.userData.id == this.user.id) {
          this.refreshUser()
        }
      });
    },


    async changeUserRole(roles) {
      if (!this.adminRoles) {
        var changetype = 'sync'
        var role = this.teamRole
      } else if (this.globalRoles.length < roles.length) {
        var changetype = 'add'
        var role = roles.filter(r => this.globalRoles.indexOf(r) < 0)
      } else {
        var changetype = 'remove'
        var role = this.globalRoles.filter(r => roles.indexOf(r) < 0)
      }


      await axios({
        method: 'post',
        url: '/api/user/roles/set',
        data: {
          user_id: this.userData.id,
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


    async updateMarriageMate () {
      if (await this.$root.$confirm(this.$t('account.confirm_change_mate'), null, 'error')) {
        await axios({
          method: 'post',      
          url: '/api/account/marriage',
          data: {
            team_id: this.team.id,
            mate1_id: this.userData.id,
            mate2_id: this.mate == undefined ? null : this.mate.id
          }
        })
        .then(response => {
            this.refreshUser()
            this.showSnackbar(this.$t('general.info_updated'), 'success')
        });
      } else {
        this.mate = this.userData.marriage_mate
      }
    },
  }
}
</script>
