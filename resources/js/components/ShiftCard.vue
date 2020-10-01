<template>
  <v-card :max-width="width" width="100%" :height="height" :style="'background-color: ' + background">
    <v-card-title  class="text-center" :style="'color: ' + (shift.location.color_code !== null ? shift.location.color_code : '')">
      {{ shift.location.name }}
    </v-card-title>

    <v-card-subtitle>
      {{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}
    </v-card-subtitle>

    <v-card-text>
      Test text
    </v-card-text>
  </v-card>
</template>


<script>
export default {
  name: 'ShiftCard',
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

}
</script>