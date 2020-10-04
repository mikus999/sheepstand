<template>
  <v-card outlined hover :width="width" :height="height" :style="'background-color: ' + background">
    <v-card-title  class="text-center" :style="'background-color: ' + (shift.location.color_code !== null ? shift.location.color_code : '')">
      {{ shift.location.name }}
    </v-card-title>

    <v-card-subtitle :style="'background-color: ' + (shift.location.color_code !== null ? shift.location.color_code : '')">
      {{ dayOfWeek($dayjs(shift.time_start).isoWeekday()) }}
      {{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}
    </v-card-subtitle>

    <v-card-text>
      <v-list dense>
        <v-list-item v-for="user in shift.users" :key="user.id" :color="shiftStatus[user.pivot.status].color" disabled>
          <v-icon class="ml-n4 mr-2" :color="shiftStatus[user.pivot.status].color">{{ shiftStatus[user.pivot.status].icon }}</v-icon>
          {{ user.name }}
        </v-list-item>
      </v-list>
    </v-card-text>

    <v-divider class="mx-3"></v-divider>

    <v-card-actions class="justify-center">
      <v-btn text outlined>Apply</v-btn> <!-- change to 'Cancel' if pending -->
      <v-btn text outlined>Trade</v-btn>
    </v-card-actions>
  </v-card>
</template>


<script>
import helper from '~/mixins/helper'

export default {
  name: 'ShiftCard',
  mixins: [helper],
  props: {
    shift: {
      type: Object
    },
    background: {
      type: String,
      default: ''
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
    }
  },

  methods: {
    dayOfWeek (daynum) {
      var days = []
      days = this.$dayjs().localeData().weekdaysShort()
      days[7] = days[0]
      return days[daynum]
    },
  }

}
</script>