<template>
  <v-card app>
    <v-toolbar
      dark
      color="primary"
    >
      <v-toolbar-title>{{ $t('schedules.assignments') }}</v-toolbar-title>

      <v-spacer />

      <span class="mr-2 text-overline">(<v-icon small class="mr-1">mdi-keyboard</v-icon>ESC)</span>
      <v-btn icon dark @click="$emit('close')">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-toolbar>

    <v-data-table
      :headers="headers"
      :items="teamUsers"
      disable-pagination
      hide-default-footer
    >
      <template v-slot:item.action="{ item }">
        <v-btn icon>
          <v-icon>mdi-plus</v-icon>
        </v-btn>
      </template>


      <template v-slot:item.fts_status="{ item }">
        {{ ftsStatus[item.fts_status].text }}
      </template>

    </v-data-table>

    <!--
    <v-list-item dense @click="addShiftUser(shift_user, item)" :disabled="getStatus_List(shift_user, item) == 3">
      <v-list-item-avatar class="ma-0">
        <v-icon small :color="(getStatus_List(shift_user, item) != 3 && shift_user.attrs['aria-selected']==='true') ? 'green' : 'red'">mdi-checkbox-blank-circle</v-icon>
      </v-list-item-avatar>
      <v-list-item-content>
        <v-list-item-title :class="getStatus_List(shift_user, item) == 3 ? 'text-decoration-line-through' : ''">{{ shift_user.item.name }}</v-list-item-title>
        <v-list-item-subtitle class="red--text">
          <span v-html="getConflictMessage(checkShiftConflicts(item, shift_user.item.shifts, true, false))"></span>
        </v-list-item-subtitle>
      </v-list-item-content>
    </v-list-item>
    -->
  </v-card>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import { helper, scheduling } from '~/mixins/helper'

export default {
  name: 'ShiftAssignments',
  mixins: [helper, scheduling],
  props: {
    shift: {
      type: [Object, Array]
    },
    teamUsers: {
      type: [Object, Array]
    }
  },
  
  data() {
    return {
      headers: [
        { 
          text: '',
          value: 'action', 
          align: 'center', 
        },        
        { 
          text: this.$t('general.name'), 
          value: 'name', 
          align: 'start', 
        },
        { 
          text: this.$t('account.fts_status'), 
          value: 'fts_status', 
          align: 'start',
        },
        {
          text: this.$t('shifts.shifts_7'), 
          value: 'shifts_30', 
          align: 'center',
        },
        {
          text: this.$t('shifts.shifts_14'), 
          value: 'shifts_30', 
          align: 'center',
        },
        {
          text: this.$t('shifts.shifts_30'), 
          value: 'shifts_30', 
          align: 'center',
        }
      ],
    }
  },

  created() {
    console.log(this.teamUsers)
  },
}
</script>