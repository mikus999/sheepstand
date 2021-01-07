<template>
  <!-- IF LARGER DEVICE, SHOW SELECT -->
  <v-menu offset-y bottom>
    <template v-slot:activator="{ on, attrs }">
      <div width="100%" v-bind="attrs" v-on="on" class="ma-0 pt-1">
        <span class="menu-label d-inline-block text-truncate" style="max-width: 150px;">{{ team != null ? team.display_name : ''}}</span>
        <v-icon class="float-right">mdi-menu-down</v-icon>
      </div>
    </template>

    <v-list dense>
      <v-list-item v-for="t in teams" :key="t.id" @click="setTeam(t)">
        <v-list-item-title>{{ t.display_name }}</v-list-item-title>
      </v-list-item>

      <v-divider class="mb-2"></v-divider>

      <v-list-item router :to="{ name: 'teams.join' }" class="text-decoration-none">
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.join_another_team') }}</v-list-item-title>
        </v-list-item-content>
        <v-list-item-icon>
          <v-icon>{{ mdiAccountMultiplePlus }}</v-icon>
        </v-list-item-icon>
      </v-list-item>
    </v-list>
  </v-menu>


</template>

<script>
import {
  mapGetters
} from 'vuex'
import axios from 'axios'
import helper from '../mixins/helper'

export default {
  name: 'TeamSelector',
  mixins: [helper],

  created() {

  },

  computed: {
    teamid: function () {
      return this.team === null ? 0 : this.team.id
    }
  }
}
</script>

<style scoped>
.menu-label{
  font-size: 10pt;
}
</style>
