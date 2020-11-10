<template>
  <v-card>
    <v-card-title>
      {{ $t('alerts.create_new_alert')}}
    </v-card-title>

    <v-card-text>
      <v-text-field v-model="message_text" :label="$t('alerts.message_text')" />
      <v-text-field v-model="link_text" :label="$t('alerts.link_text')" />
      <v-select v-model="named_route" :label="$t('alerts.named_route')" :items="routes" item-text="path" item-value="name" />

      <v-switch v-model="show_banner" :label="$t('alerts.show_as_banner')" />
      
      <v-card v-if="show_banner" outlined class="pa-8">
        <v-card-title>
          {{ $t('alerts.banner_options')}}
        </v-card-title>

        <v-card-text>
          <v-select v-model="type" :label="$t('alerts.type')" :items="types" />
          <v-switch v-model="dismissable" :label="$t('alerts.dismissable')" />
          <v-switch v-model="outlined" :label="$t('alerts.outlined')" />

          <v-switch v-model="custom_color" :label="$t('alerts.custom_color')" />
          <v-menu 
            v-model="menu" 
            v-if="custom_color"
            top 
            nudge-bottom="105" 
            nudge-left="16" 
            :open-on-click="true" 
            :close-on-content-click="false">

            <template v-slot:activator="{ on }">
              <v-text-field 
                v-model="color_code" 
                :label="$t('general.color') + ' (' + $t('general.optional') + ')'" 
                v-on="on" 
                prepend-icon="mdi-palette" 
                hide-details 
                >

                <template v-slot:prepend-inner>
                  <v-icon :color="color_code">mdi-square-rounded</v-icon>
                </template>

                <template v-slot:append>
                  <v-icon @click.prevent="color_code=null">mdi-close</v-icon>
                </template>
              </v-text-field>
            </template>
            <v-card>
              <v-card-text class="pa-0">
                <v-color-picker 
                  v-model="color_code" 
                  flat 
                  hide-canvas
                  hide-inputs
                  hide-mode-switch
                  mode="hexa"
                  show-swatches
                />
              </v-card-text>
              <v-card-actions>
                <v-spacer />
                <v-btn text @click="menu = false">{{ $t('general.ok') }}</v-btn>
              </v-card-actions>
            </v-card>
          </v-menu>

          <div class="mt-16">
            <v-alert 
              :type="type"
              :color="custom_color ? color_code : null"
              :icon="icon"
              :dismissible="dismissable"
              :outlined="outlined"
            >
              <v-row align="center">
                <v-col cols="12">
                  {{ message_text }}
                </v-col>
                <v-col cols="12">
                  <div v-if="link_text !== null && link_text !== ''" class="text-center">
                    <v-btn class="mx-auto">{{ link_text }}</v-btn>
                  </div>
                </v-col>
              </v-row>
            </v-alert>
          </div>
        </v-card-text>
      </v-card>
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
      menu: false,
      message_text: null,
      link_text: null,
      named_route: null,
      custom_color: false,
      color_code: '#ffffff',
      type: 'info',
      icon: null,
      dismissable: true,
      outlined: true,
      show_banner: true,
      display_until: null,
      routes: [],
      types: [
        'success',
        'info',
        'error',
        'warning'
      ]
    }
  },

  created () {
    this.getRoutes()
  },

  methods: {
    getRoutes() {
      this.$router.options.routes.forEach(route => {
        if ((route.meta.roles.length == 0 || this.$is(route.meta.roles)) && !route.props) {
          this.routes.push({
            name: route.name,
            path: route.path
          })
        }
      })
    }
  }
}
</script>