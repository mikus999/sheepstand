<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$t('schedules.schedule')"></PageTitle>
    </v-row>


    <v-row>
      <ScheduleEdit v-if="!pageLoad.value" :schedule="schedule" />
    </v-row>

    <!-- PAGE LOAD OVERLAY -->
    <v-overlay :value="pageLoad.value" :opacity=".9" class="text-center" z-index="900">
      <v-progress-circular
        indeterminate
        color="primary"
        size="64"
      ></v-progress-circular>
      <h1 class="mt-16 text-h4">{{ pageLoad.text }}</h1>
    </v-overlay>

  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling } from '~/mixins/helper'
import ScheduleEdit from '~/components/ScheduleEdit.vue'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, scheduling],
  props: {
    id: {
      type: [String, Number],
      required: true,
    }
  },
  components: {
    ScheduleEdit
  },

  data () {
    return {
      pageLoad: {
        value: true,
        progress: 0,
        text: ''
      },
    }
  },
  
  computed: {
    ...mapGetters({
      schedule: 'scheduling/schedule',
      shifts: 'scheduling/shifts',
      team_availability: 'scheduling/team_availability',
      team_users: 'scheduling/team_users',
    }),

    isTemplate () {
      return this.schedule.status == 9
    },

  },

  created () {
    this.initialize()
  },

  methods: {
    async initialize () {
      await this.getSchedule()
      
      if (!this.isTemplate) {
        await this.getTeamUsers()
        this.getTeamAvailability()
      }


      this.pageLoad.text = this.$t('schedules.loading_complete')
      this.pageLoad.progress = 100
      this.pageLoad.value = false
    },


    async getSchedule () {
      this.pageLoad.text = this.$t('schedules.loading_schedule')
      this.pageLoad.progress = 5

      await axios.get('/api/schedules/show/' + this.id)
        .then(response => {
          this.storeSchedule(response.data)
          this.storeShifts(response.data.shifts)
        })

    },

    async getTeamUsers () {
      this.pageLoad.text = this.$t('schedules.loading_users')

      await axios.get('/api/teams/' + this.team.id + '/users/')
        .then(response => {
          this.storeTeamUsers(response.data)
          this.pageLoad.progress = 80
        })
    },

    async getTeamAvailability () {
      this.pageLoad.text = this.$t('schedules.loading_availability')

      await axios.get('/api/teams/' + this.team.id + '/availability/')
        .then(response => {
          this.storeTeamAvailability(response.data.users)
          this.pageLoad.progress = 60
        })
    },

  },
}

</script>
