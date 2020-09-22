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
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  created () {
    const testPerm = 'manage_schedules'
    var isAllowed = false
    const siteRoles = JSON.parse(sessionStorage.getItem('roles'))
    const sitePermissions = siteRoles.map(p => p['permissions'])
    sitePermissions.forEach(function (arrayItem) {
      const arrayPermissions = arrayItem.map(t => t['name'])
      const result = arrayPermissions.indexOf(testPerm)
      if (result >= 0) {
        isAllowed = true
      }
    })

    console.log(isAllowed)
  },

}
</script>
