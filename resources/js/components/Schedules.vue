<template>
  <v-card width="100%">
    <v-data-table :headers="templates ? templateHeaders : schedHeaders" :items="schedData" sort-by="date_start" sort-desc>
      <template v-slot:top>
        <v-toolbar flat v-if="!templates">
          <v-toolbar-title v-show="$vuetify.breakpoint.smAndUp">{{ $t('schedules.shift_schedules') }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn 
            color="secondary" 
            class="mb-2" 
            :block="$vuetify.breakpoint.xs"
            @click="dialog = true" 
          >
            <v-icon left small>mdi-calendar-plus</v-icon>
            {{ $t('schedules.create_new_schedule') }}
          </v-btn>

          <v-dialog v-model="dialog" max-width="500px">
            <v-card>
              <v-card-title>
                <span class="headline">{{ $t('schedules.create_new_schedule') }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-row>
                    <v-col cols="12">
                      <v-menu ref="menu" v-model="menu" :close-on-content-click="false" :return-value.sync="date"
                        transition="scale-transition" offset-y min-width="290px">

                        <template v-slot:activator="{ on, attrs }">
                          <v-text-field v-model="newSchedDate" :label="$t('schedules.choose_start_date')" prepend-icon="mdi-calendar" readonly
                            v-bind="attrs" v-on="on"></v-text-field>
                        </template>
                        <v-date-picker 
                          v-model="newSchedDate" 
                          no-title 
                          scrollable 
                          :locale="locale"
                          :first-day-of-week="$dayjs().localeData().firstDayOfWeek()"
                          :allowed-dates="allowedDates"
                        >
                          <v-spacer></v-spacer>
                          <v-btn text color="primary" @click="menu = false">{{ $t('general.cancel') }}</v-btn>
                          <v-btn text color="primary" @click="$refs.menu.save(date)">{{ $t('general.ok') }}</v-btn>
                        </v-date-picker>
                      </v-menu>

                    </v-col>
                  </v-row>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue darken-1" text @click="close">{{ $t('general.cancel') }}</v-btn>
                <v-btn color="blue darken-1" text @click="save">{{ $t('general.create') }}</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>

        <v-toolbar flat v-else>
          <v-toolbar-title>{{ $t('schedules.templates') }}</v-toolbar-title>
        </v-toolbar>
      </template>

      <template v-slot:item.date_start="{ item }">
        {{ item.date_start | formatDate }}
      </template>

      <template v-slot:item.created_at="{ item }">
        {{ item.created_at | formatDate }}
      </template>

      <template v-slot:item.status="{ item }">
        <div :class="getStatusColor(item)">{{ getStatusText(item) }}</div>
      </template>

      <template v-slot:item.shifts_count="{ item }">
        {{ item.shifts_count }}
      </template>
      
      <template v-slot:item.schedule_actions="{ item }">
        <v-btn icon small @click="editSched(item)">
          <v-icon small>mdi-pencil</v-icon>
        </v-btn>

        <v-btn icon small @click="editAssignments(item)" :disabled="item.status < 1"><v-icon small>mdi-account-multiple-plus</v-icon></v-btn>

        <v-btn icon small @click="deleteSched(item)">
          <v-icon small>mdi-delete</v-icon>
        </v-btn>
      </template>

      <template v-slot:item.template_actions="{ item }">
        <v-btn icon small @click="editSched(item)">
          <v-icon small>mdi-pencil</v-icon>
        </v-btn>

        <v-btn icon small @click="deleteSched(item)">
          <v-icon small>mdi-delete</v-icon>
        </v-btn>
      </template>

    </v-data-table>
  </v-card>

</template>

<script>
import axios from 'axios'
import Form from 'vform'
import helper from '~/mixins/helper'


export default {
  name: 'Schedules',
  mixins: [helper],
  props: {
    templates: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      dialog: false,
      schedHeaders: [
        { text: this.$t('schedules.status'), align: 'start', value: 'status' },
        { text: this.$t('schedules.start_date'), value: 'date_start' },
        { text: this.$t('schedules.shifts'), value: 'shifts_count', align: 'center', sortable: false },
        { text: this.$t('general.actions'), value: 'schedule_actions', sortable: false },
      ],
      templateHeaders: [
        { text: this.$t('schedules.template_name'), align: 'start', value: 'status' },
        { text: this.$t('schedules.template_created'), value: 'created_at' },
        { text: this.$t('general.actions'), value: 'template_actions', sortable: false },
      ],
      schedData: [],
      newSchedDate: '',
      date: new Date().toISOString().substr(0, 10),
      menu: false,
    }
  },

  created () {
    this.getSchedData()
  },

  methods: {

    async getSchedData () {
      var url = null
      if (this.templates) {
        url = '/api/schedules/templates/' + this.team.id
      } else {
        url = '/api/schedules/' + this.team.id
      }

      await axios.get(url)
        .then(response => {
          this.schedData = response.data
        })
    },


    getDayIcon(status) {
      return (status ? 'mdi-check-bold' : 'mdi-close-thick')
    },

    getDayColor(status) {
      return (status ? 'green' : 'red')
    },

    allowedDates(val) {
      return this.$dayjs(val).day() === 1
    },

    getStatusColor(item) {
      var textColor = "black--text"
      if (this.scheduleStatus[item.status] != undefined) {
        textColor = this.scheduleStatus[item.status].color + '--text'
      }
      return textColor
    },

    getStatusText(item) {
      var textString = null
      if (!this.templates) {
        textString = this.scheduleStatus[item.status].text
      } else {
        textString = item.schedule_template.name
      }
      return textString
    },

    editSched (schedule) {
      this.$router.push({
          name: 'schedules.edit',
          params: {
              id: schedule.id
          }
      }) 
    },

    editAssignments (schedule) {
      this.$router.push({
          name: 'schedules.assignments',
          params: {
              id: schedule.id
          }
      }) 
    },

    async deleteSched (sched) {
      const index = this.schedData.indexOf(sched.id)
      if (await this.$root.$confirm(this.$t('schedules.confirm_delete_schedule'), null, 'error')) {
        await axios.delete('/api/schedules/' + sched.id)
          .then(response => {
            this.showSnackbar(this.$t('schedules.success_delete_schedule'), 'success')
            this.getSchedData()
          })


      }
    },

    close () {
      this.dialog = false
    },

    save () {
      if (this.newSchedDate !== '') {
        this.newSchedDate = this.$dayjs(this.newSchedDate).startOf('isoWeek').format("YYYY-MM-DD")

        const formData = new FormData()
        formData.append('user_id', this.user.id)
        formData.append('team_id', this.team.id)
        formData.append('date_start', this.newSchedDate)
        axios.post('/api/schedules', formData)
          .then(response => {
            this.showSnackbar(this.$t('schedules.success_create_schedule'), 'success')
            this.getSchedData()
          })
      }

      this.newSchedDate = ''
      this.close()
    }
  }
}
</script>

<style lang="scss" scoped>

</style>