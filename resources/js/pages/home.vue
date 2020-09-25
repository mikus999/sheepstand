<template>
  <v-container fluid>
    <v-row>
      <h4>Home</h4>

    </v-row>
  </v-container>
</template>

<script>
import helper from '~/mixins/helper'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],

  created () {
    
    const testPerm = 'manage_schedules'
    var isAllowed = false

    const sitePermissions = this.siteRoles.map(p => p['permissions'])

    sitePermissions.forEach(function (item) {
      const arrayPermissions = item.map(t => t['name'])
      const result = arrayPermissions.indexOf(testPerm)
      if (result >= 0) {
        console.log(this.siteRoles())

        isAllowed = true
      }
    })

    console.log(isAllowed)
    
  },

}
</script>
