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

  created() {
    
    console.log(this.can('view_schedules'))


    const teamRoles = this.roles[this.team.id]
    const globalRoles = this.roles['global']
    const myRoles = teamRoles.concat(globalRoles)
    console.log(myRoles)
  },

  methods: {
    can (permName) {
      var rolesAllowed = []
      var isAllowed = false

      // Find which roles contain this permission
      this.siteRoles.forEach(item => {
        const sitePermissions = item.permissions
        const arrayPermissions = sitePermissions.map(t => t['name'])
        const foundRole = arrayPermissions.indexOf(permName)
        if (foundRole >= 0) {
          var roleName = item.name
          rolesAllowed.push(roleName)
          isAllowed = true
        }
      })

      return rolesAllowed

    }
  }

}
</script>
