<template>
  <v-card width="100%" :flat="$vuetify.breakpoint.xs">
    <v-card-text>
      <v-row>
          <v-col>
          <!-- Number of Participants slider -->
          <v-slider 
            v-model="preferences.max_weekly_shifts" 
            :thumb-size="20" 
            thumb-label="always"
            min="0" 
            max="10" 
            ticks="always" 
            tick-size="4" 
            :prepend-icon="icons.mdiCalendarMultiselect"
            :label="$t('account.max_weekly_shifts')"
            @change="changeSetting('max_weekly_shifts', 'int')"
          />
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script>
import axios from 'axios'
import { helper, scheduling } from '~/mixins/helper'

export default {
  name: 'UserPreferences',
  mixins: [helper, scheduling],
  props: {
    targetUser: {
      type: Object
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    height: {
      type: [String, Number],
      default: '100%'
    },
  },

  data () {
    return {
      preferences: {
        max_weekly_shifts: this.targetUser.max_weekly_shifts,
      }
    }
  },

  created() {

  },

  methods: {
    async changeSetting (setting, valType) {
      var val = ''

      if (valType === 'bool') {
        val = this.preferences[setting] === null ? '0' : '1'
      } else {
        val = this.preferences[setting]
      }


      await axios({
        method: 'post',      
        url: '/api/account/settings/update',
        data: {
          user_id: this.targetUser.id,
          team_id: this.team.id,
          setting: setting,
          value: val
        }
      })        
      .then(response => {
        this.refreshUser()
        this.showSnackbar(this.$t('general.info_updated'), 'success')
      });
      

    },



  }
}
</script>