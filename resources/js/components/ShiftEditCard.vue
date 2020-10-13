<template>
  <v-card :id="shift.id" class="shift mt-5 handle" :color="shift.location.color_code">
    <v-card-text class="shift-body text-center pa-0">
      <v-row dense>
        <v-col cols=2><v-icon small>mdi-map-marker</v-icon></v-col>
        <v-col cols=8 class="font-weight-bold">{{ shift.location.name }}</v-col>
      </v-row>
      <v-row dense>
        <v-col cols=2><v-icon small>mdi-clock</v-icon></v-col>
        <v-col cols=8 class="font-weight-bold">{{ shift.time_start | formatTime }} - {{ shift.time_end | formatTime }}</v-col>
      </v-row>
      <v-row dense>
        <v-col cols=2><v-icon small>mdi-account-tie</v-icon></v-col>
        <v-col cols=3 offset="1">
          <v-chip x-small>{{ shift.min_participants }}</v-chip><br>
          <span>{{ $t('general.min') }}</span>
        </v-col>
        <v-col cols=2>
          <v-chip x-small>{{ shift.max_participants }}</v-chip><br>
          <span>{{ $t('general.max') }}</span>
        </v-col>
      </v-row>
    </v-card-text>
    
    <v-divider class="pa-0 ma-0" />

    <v-card-actions class="pa-0">
      <v-row dense>
        <v-col>
          <v-btn icon @click="showShiftDialog()">
            <v-icon small>mdi-pencil</v-icon>
          </v-btn>
        </v-col>
        <v-col>
          <v-btn icon @click="deleteShift(shift.id)">
            <v-icon small>mdi-delete</v-icon>
          </v-btn>
        </v-col>
        <v-col>
          <v-btn icon @click="duplicateShift(shift.id)">
            <v-icon small>mdi-content-duplicate</v-icon>
          </v-btn>
        </v-col>
      </v-row>
    </v-card-actions>


    <v-dialog :value="dialog" @click:outside="closeShiftDialog()" width="500px">
      <ShiftNewCard :shift="shift" :schedule="schedule" edit v-on:update="$emit('update', $event)" v-on:close="closeShiftDialog()" />
    </v-dialog>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import ShiftNewCard from '~/components/ShiftNewCard.vue'

export default {
  name: 'ShiftEditCard',
  mixins: [helper],
  components: {
    ShiftNewCard
  },
  props: {
    shift: {
      type: Object
    },
    schedule: {
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
    onlyinfo: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      dialog: false,

    }
  },

  created () {

  },

  methods: {

    async duplicateShift (id) {
      var newShiftData = []

      await axios.get('/api/schedules/' + this.schedule.id + '/shifts/' + id)
        .then(response => {
          newShiftData = response.data

          const formData = new FormData()
          formData.append('location_id', newShiftData.location_id)
          formData.append('time_start', newShiftData.time_start)
          formData.append('time_end', newShiftData.time_end)
          formData.append('min_participants', newShiftData.min_participants)
          formData.append('max_participants', newShiftData.max_participants)
          axios.post('/api/schedules/' + this.schedule.id + '/shifts', formData)
            .then(response => {
              this.$emit('update', response.data.schedule)
            })
        })
    },
    
    async deleteShift (id) {
      if (await this.$root.$confirm(this.$t('schedules.confirm_delete_shift'), null, 'error')) {
        await axios.delete('/api/schedules/' + this.schedule.id + '/shifts/' + id)
          .then(response => {
            this.showSnackbar(this.$t('schedules.success_delete_shift'), 'success')
            this.$emit('update', response.data.schedule)
          })

      }
    },

    showShiftDialog () {
      this.dialog = true
    },

    closeShiftDialog () {
      this.dialog = false
    },

  }
}
</script>