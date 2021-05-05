<template>
  <v-card>
    <v-card-title class="justify-center">
      <v-icon left>{{ icons.mdiRotateOrbit }}</v-icon>
      {{ $t('schedules.auto_assign') }}
    </v-card-title>

    <v-card-text class="mt-6">
      <div v-if="!pageLoad.value">
      <v-row>
        <v-col>
          <h3>{{ $t('schedules.aa_min_or_max') }}</h3>
          <v-radio-group v-model="min_or_max" row>
            <v-radio value="MIN" :label="$t('general.minimum')" />
            <v-radio value="MAX" :label="$t('general.maximum')" />
          </v-radio-group>
        </v-col>
      </v-row>

      <v-row>
        <v-col>
          <h3>{{ $t('schedules.aa_reset') }}</h3>

          <v-switch v-model="reset_all" />
        </v-col>
      </v-row>
      </div>


      <div v-else class="text-center">
        <v-progress-circular
          indeterminate
          color="primary"
          size="64"
        ></v-progress-circular>
        <h3 class="mt-8 text-h4">{{ pageLoad.text }}</h3>
      </div>
    </v-card-text>


    <v-card-actions>
      <v-spacer></v-spacer>
      <v-btn color="primary" :disabled="pageLoad.value" text v-on:click="$emit('close')">
        {{ $t('general.close' ) }}
      </v-btn>
      <v-btn color="green" :disabled="pageLoad.value" v-on:click="runAutoAssign()">
        {{ $t('schedules.aa_start_process' ) }}
      </v-btn>
    </v-card-actions>

  </v-card>
</template>

<script>
import axios from 'axios'
import { helper, scheduling } from '~/mixins/helper'

export default {
  name: 'AutoAssign',
  mixins: [helper, scheduling],
  props: {
    schedule: {
      type: [Object, Array]
    },
  },
  data() {
    return {
      min_or_max: 'MIN',
      reset_all: 1,
      pageLoad: {
        value: false,
        progress: 0,
        text: ''
      },
    }
  },

  created() {

  },

  methods: {
    async runAutoAssign() {
      this.pageLoad.value = true
      this.pageLoad.text = this.$t('schedules.loading_complete')

      await axios({
        method: 'post',      
        url: '/api/assignments/auto',
        data: {
          team_id: this.team.id,
          schedule_id: this.schedule.id,
          reset: this.reset_all,
          min_or_max: this.min_or_max
        }
      })
      .then(response => {
        this.storeSchedule(response.data.data.schedule)
        this.storeShifts(response.data.data.schedule.shifts)
        this.showSnackbar(this.$t('general.info_updated'), 'success')
        this.pageLoad.value = false
        this.$emit('update')
      })
      .catch(error => {
        console.log(error)
      })
    },

  }
}
</script>