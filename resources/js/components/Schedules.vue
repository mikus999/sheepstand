<template>
  <v-card width="100%">
    <v-data-table 
      :headers="templates ? templateHeaders : schedHeaders" 
      :items="getTableItems" 
      :loading="pageLoad"
      sort-by="date_start" 
      sort-desc
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>
            <v-icon left>{{ templates ? 'mdi-calendar-star' : 'mdi-calendar-week' }}</v-icon>
            {{ templates ? $t('schedules.templates') : $t('schedules.weekly_schedules') }}
          </v-toolbar-title>

          <v-spacer></v-spacer>

          <v-btn 
            color="secondary" 
            class="mb-2" 
            @click="templates ? dialog2 = true : dialog = true" 
          >
            <v-icon 
              :left="$vuetify.breakpoint.smAndUp"
              :small="$vuetify.breakpoint.smAndUp"
            >mdi-calendar-plus</v-icon>
            <span v-if="$vuetify.breakpoint.smAndUp">
              {{ templates ? $t('schedules.new_template') : $t('schedules.create_new_schedule') }}
            </span>
          </v-btn>
        </v-toolbar>

                  
        <v-switch 
          v-if="!templates"
          v-model="sw_show_archived" 
          class="mx-4" 
          :label="$t('schedules.show_archived_old')" 
        />
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
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon @click="editSched(item)" v-bind="attrs" v-on="on">
              <v-icon>mdi-calendar-edit</v-icon>
            </v-btn>
          </template>
          <span>{{ $t('general.edit') }}</span>
        </v-tooltip>

        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">        
            <v-btn icon @click="deleteSched(item)" v-bind="attrs" v-on="on">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
          <span>{{ $t('general.delete') }}</span>
        </v-tooltip>
      </template>


      <template v-slot:item.template_actions="{ item }">
        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">
            <v-btn icon @click="editSched(item)" v-bind="attrs" v-on="on">
              <v-icon>mdi-calendar-edit</v-icon>
            </v-btn>
          </template>
          <span>{{ $t('general.edit') }}</span>
        </v-tooltip>

        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">        
            <v-btn icon @click="convertToSchedule(item)" v-bind="attrs" v-on="on">
              <v-icon>mdi-calendar-plus</v-icon>
            </v-btn>
          </template>
          <span>{{ $t('schedules.make_new_schedule') }}</span>
        </v-tooltip>

        <v-tooltip bottom>
          <template v-slot:activator="{ on, attrs }">        
            <v-btn icon @click="deleteSched(item)" v-bind="attrs" v-on="on">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
          <span>{{ $t('general.delete') }}</span>
        </v-tooltip>

      </template>

    </v-data-table>



    <!-- NEW SCHEDULE DIALOG -->
    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">
            {{ $t('schedules.create_new_schedule') }}
          </span>
        </v-card-title>

        <v-card-text>
          <v-menu 
            ref="menu" 
            v-model="menu" 
            :close-on-content-click="false" 
            :return-value.sync="date"
            transition="scale-transition" 
            offset-y 
            min-width="290px"
          >

            <template v-slot:activator="{ on, attrs }">
              <v-text-field 
                v-model="newSchedDate" 
                :label="$t('schedules.choose_start_date')" 
                prepend-icon="mdi-calendar-week-begin" 
                readonly
                v-bind="attrs" 
                v-on="on"
              ></v-text-field>
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
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" text @click="close">{{ $t('general.cancel') }}</v-btn>
          <v-btn color="primary" @click="createSchedule">{{ $t('general.create') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>



    <!-- NEW TEMPLATE DIALOG -->
    <v-dialog v-model="dialog2" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="headline">
            {{ $t('schedules.new_template') }}
          </span>
        </v-card-title>

        <v-card-text>
          <v-text-field 
            v-model="newTemplateName" 
            :label="$t('schedules.template_name')" 
            prepend-icon="mdi-form-textbox"
          ></v-text-field>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" text @click="close">{{ $t('general.cancel') }}</v-btn>
          <v-btn color="primary" @click="createSchedule">{{ $t('general.create') }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>

</template>

<script>
import axios from 'axios'
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
      pageLoad: true,
      dialog: false,
      dialog2: false,
      schedHeaders: [
        { text: this.$t('schedules.status'), align: 'start', value: 'status' },
        { text: this.$t('schedules.start_date'), value: 'date_start' },
        { text: this.$t('schedules.shifts'), value: 'shifts_count', align: 'center', sortable: false },
        { text: this.$t('general.actions'), value: 'schedule_actions', sortable: false },
      ],
      templateHeaders: [
        { text: this.$t('schedules.template_name'), align: 'start', value: 'template_name' },
        { text: this.$t('schedules.template_created'), value: 'created_at' },
        { text: this.$t('general.actions'), value: 'template_actions', sortable: false },
      ],
      schedData: [],
      newSchedDate: null,
      newTemplateId: null,
      newTemplateName: null,
      date: new Date().toISOString().substr(0, 10),
      menu: false,
      sw_show_archived: this.templates
    }
  },


  computed: {
    getTableItems() {
      if (this.sw_show_archived) {
        return this.schedData
      } else {
        return this.schedData.filter(sched => (sched.status != 3) && (this.$dayjs(sched.date_start).isAfter(this.$dayjs().subtract(14, 'day'))))
      }
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
          this.pageLoad = false
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
      textString = this.scheduleStatus[item.status].text
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
      const confirm_msg = this.templates ? this.$t('schedules.confirm_delete_template') : this.$t('schedules.confirm_delete_schedule')
      const success_msg = this.templates ? this.$t('schedules.success_delete_template') : this.$t('schedules.success_delete_schedule')

      if (await this.$root.$confirm(confirm_msg, null, 'error')) {
        await axios.delete('/api/schedules/' + sched.id)
          .then(response => {
            this.showSnackbar(success_msg, 'success')
            this.getSchedData()
          })
      }
    },

    close () {
      this.dialog = false
      this.dialog2 = false
      this.newSchedDate = ''
      this.newTemplateId = null
      this.newTemplateName = null
    },

    async createSchedule () {
      var url = null
      var success_msg = null
      var validate = false

      if (this.templates && this.newTemplateId == null) {
        // If we're creating a new template
        url = '/api/schedules/templates' 
        success_msg = this.$t('schedules.success_create_template')
        this.newSchedDate = null
        validate = this.newTemplateName != null

      } else if (this.newTemplateId) {
        // If we're creating a schedule from a template
        url = '/api/schedules/templates/' + this.newTemplateId + '/copy'
        success_msg = this.$t('schedules.success_create_schedule')
        if (this.newSchedDate != '') {
          this.newSchedDate = this.$dayjs(this.newSchedDate).startOf('isoWeek').format("YYYY-MM-DD")
          validate = true
        } 

      } else {
        // If we're creating a blank schedule
        url = '/api/schedules'
        success_msg = this.$t('schedules.success_create_schedule')
        if (this.newSchedDate != '') {
          this.newSchedDate = this.$dayjs(this.newSchedDate).startOf('isoWeek').format("YYYY-MM-DD")
          validate = true
        }
      }


      if (validate) {
        await axios({
          method: 'post',      
          url: url,
          data: {
            team_id: this.team.id,
            date_start: this.newSchedDate,
            template_name: this.newTemplateName,
            template_id: this.newTemplateId // NULL unless we're creating schedule from template
          }
        })
        .then(response => {
          this.showSnackbar(success_msg, 'success')

          if (this.newTemplateId) {
            // This will rerender both Schedule components, to show newly created schedule
            this.$emit('updated')
          } else {
            this.getSchedData()
          }
        })

        this.close()
      }

    },


    async convertToSchedule(template) {
      this.newTemplateId = template.id
      this.dialog = true
    },

  }
}
</script>

<style lang="scss" scoped>

</style>