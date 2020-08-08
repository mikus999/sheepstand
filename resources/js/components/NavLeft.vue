<template>
  <v-list>
    <v-list-item>
      <v-list-item-content>
        <v-list-item-title class="title">
          <img src="dist/img/cplogo.png" width="30" height="30" class="d-inline-block align-top ml-1 mr-2">

          <span class="head-thick">CART</span>
          <span class="head-thin ml-n1">PLAN</span>
        </v-list-item-title>
      </v-list-item-content>
    </v-list-item>

    <v-divider />

    <v-list-item router :to="{ name: 'home' }">
      <v-list-item-icon>
        <v-icon>mdi-view-dashboard</v-icon>
      </v-list-item-icon>
      <v-list-item-content>
        <v-list-item-title>Dashboard</v-list-item-title>
      </v-list-item-content>
    </v-list-item>

    <v-list-group v-for="item in items" :key="item.title" :prepend-icon="item.icon" no-action>
      <template v-slot:activator>
        <v-list-item-content>
          <v-list-item-title>{{ item.title }}</v-list-item-title>
        </v-list-item-content>
      </template>

      <v-list-item v-for="subitem in item.subitems" :key="subitem.title" router :to="{ name: subitem.route }">
        <v-list-item-content>
          <v-list-item-title v-text="subitem.title" />
        </v-list-item-content>
      </v-list-item>
    </v-list-group>

          <v-switch
        v-model="$vuetify.theme.dark"
        hide-details
        inset
        label="Theme Dark"
      ></v-switch>
  </v-list>
</template>

<script>
export default {
  data () {
    return {
      items: [
        {
          title: 'Teams',
          icon: 'mdi-account-group',
          subitems: [
            { title: 'Join Team', icon: 'mdi-account-multiple-plus', route: 'teams.join' }
          ]
        },
        {
          title: 'Team Settings',
          icon: 'mdi-cog'
        },
        {
          title: 'Account',
          icon: 'mdi-account'
        }
      ]
    }
  },

  toggleDarkMode () {
    this.$vuetify.theme.dark = !this.$vuetify.theme.dark
  }
}
</script>
