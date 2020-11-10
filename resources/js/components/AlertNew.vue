<template>
  <v-card>
    <v-card-title>
      {{ $t('alerts.create_new_alert')}}
    </v-card-title>

    <v-card-text>
      <v-text-field v-model="message_text" :label="$t('alerts.message_text')" />
      <v-text-field v-model="link_text" :label="$t('alerts.link_text')" />
      <v-select v-model="named_route" :items="routes" item-text="route" item-value="name" />

      <v-switch v-model="dismissable" :label="$t('alerts.dismissable')" />
      <v-switch v-model="outlined" :label="$t('alerts.outlined')" />
      <v-switch v-model="show_banner" :label="$t('alerts.show_as_banner')" />

    </v-card-text>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'AlertNew',
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    
  },
  
  data () {
    return {
      message_text: null,
      link_text: null,
      named_route: null,
      color: null,
      type: 'info',
      icon: null,
      dismissable: true,
      outlined: true,
      show_banner: false,
      display_until: null,
      routes: []
    }
  },

  created () {
    this.getRoutes()
  },

  methods: {
    getRoutes() {
      this.$router.options.routes.forEach(route => {
        this.routes.push({
          name: route.name,
          path: route.path
        })
      })
    }
  }
}
</script>