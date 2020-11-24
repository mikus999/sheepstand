<template>
  <v-card outlined hover :width="width">
    <v-card-title class="justify-center text-h6">
      {{ $t('account.user_security') }}
    </v-card-title>

    <v-card-subtitle class="text-center font-weight-bold pt-4">
      {{ this.data.name }}
    </v-card-subtitle>

    <v-card-text>
      <v-radio-group>
        <v-radio v-for="sr in siteRoles.filter(role => role.global == false)" :key="sr.id" :label="$t('roles.' + sr.name)"></v-radio>
      </v-radio-group>
    </v-card-text>
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
  },

  data() {
    return {
      userRoles: null,
      teamRoles: null
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
        this.teamRoles = this.userRoles[this.team.id]
      })
    },
  },

}
</script>
