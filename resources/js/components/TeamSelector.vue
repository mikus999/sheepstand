<template>
  <v-select v-if="!$vuetify.breakpoint.mobile" :items="user.teams" item-text="display_name" item-value="id" :value="team.id" return-object @change="setTeam($event, 'home')" outlined dense prepend-icon="mdi-account-group select-item">
    <template v-slot:append-item>
      <v-divider class="mb-2"></v-divider>

      <v-list-item router :to="{ name: 'teams.join' }" class="text-decoration-none">
        <v-list-item-content>
          <v-list-item-title>{{ $t('menu.join_another_team') }}</v-list-item-title>
        </v-list-item-content>
        <v-list-item-icon>
          <v-icon>mdi-account-multiple-plus</v-icon>
        </v-list-item-icon>
      </v-list-item>
    </template>
  </v-select>

  <!-- IF MOBILE DEVICE, SHOW DROPDOWN MENU ON NAVBAR INSTEAD OF SELECT -->
  <v-menu v-else offset-y bottom left>
    <template v-slot:activator="{ on, attrs }">
      <v-btn dark icon v-bind="attrs" v-on="on">
        <v-icon>mdi-account-group</v-icon>
      </v-btn>
    </template>

    <v-list>
      <v-list-item v-for="t in teams" :key="t.id" @click="setTeam(t.id)">
        <v-list-item-title>{{ t.display_name }}</v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>

</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'
import helper from '../mixins/helper'

export default {
  name: 'TeamSelector',
  mixins: [helper],

  created () {
    
  },

  computed: {
    teamid: function () {
      return this.team === null ? '' : this.team.id
    }
  }
}
</script>

<style scoped>
  .v-input {
    font-size: 10pt;
  }
</style>