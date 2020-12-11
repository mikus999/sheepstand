<template>
  <v-card hover :width="width">
    <v-card-title class="justify-center text-h6">
      <v-icon class="mr-3">mdi-shield-account</v-icon>
      {{ $t('account.fts_status') }}
    </v-card-title>

    <v-card-subtitle class="text-center font-weight-bold pt-4">
      {{ this.data.name }}
    </v-card-subtitle>

    <v-card-text class="my-5">
      <v-radio-group v-model="userData.fts_status" class="my-0" @change="updateFTS()">
        <v-radio v-for="fts in ftsStatus" :key="fts.value" :label="fts.text" />
      </v-radio-group>
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
  name: 'FTSStatus',
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
  },

  data() {
    return {
      userData: []
    }
  },

  created() {
    this.userData = this.data
  },

  methods: {

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


  }
}
</script>
