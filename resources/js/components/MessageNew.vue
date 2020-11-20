<template>
  <v-card>
    <v-card-title>
      {{ $t('messages.create_new_message')}}
    </v-card-title>

    <v-card-text>
      <v-radio-group
        v-model="system_message"
        v-if="$is('super_admin')"
        row>
        <v-radio label="Local Message" :value="false"></v-radio>
        <v-radio label="System Message" :value="true"></v-radio>
      </v-radio-group>

      <v-textarea 
        v-model="message_text" 
        v-if="!system_message" 
        :label="$t('messages.message_text')"
        auto-grow
        rows=1 />

      <v-select 
        v-model="message_text_i18n" 
        v-if="system_message"
        :label="$t('messages.message_text_i18n')" 
        :items="i18n_strings" 
        item-text="text" 
        item-value="value" 
        menu-props="overflowY"
        clearable />

      <v-select 
        v-model="named_route" 
        :label="$t('messages.named_route') + ' (' + $t('general.optional') + ')'" 
        :items="routes" 
        item-text="path" 
        item-value="name" 
        clearable />

      <v-menu
        ref="date_menu"
        v-model="date_menu"
        :close-on-content-click="true"
        transition="scale-transition"
        offset-y
        min-width="290px"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
            v-model="expires_on"
            :label="$t('messages.display_until') + ' (' + $t('general.optional') + ')'"
            prepend-icon="mdi-calendar"
            readonly
            v-bind="attrs"
            v-on="on"
            clearable
          ></v-text-field>
        </template>
        <v-date-picker
          ref="picker"
          v-model="expires_on"
        ></v-date-picker>
      </v-menu>


      <v-switch v-model="show_inbox" :label="$t('messages.show_in_inbox')" disabled />
      <v-switch v-model="send_telegram" :label="$t('messages.send_to_telegram')" :disabled="!notificationsEnabled" />
      <v-switch v-model="show_banner" :label="$t('messages.show_as_banner')" />
      
      <v-card v-if="show_banner" outlined class="pa-8">
        <v-card-title>
          {{ $t('messages.banner_options')}}
        </v-card-title>

        <v-card-text>
          <v-switch v-model="dismissable" :label="$t('messages.dismissable')" />
          <v-switch v-model="outlined" :label="$t('messages.outlined')" />

          <v-menu 
            v-model="color_menu" 
            v-if="custom_color"
            top 
            nudge-bottom="105" 
            nudge-left="16" 
            :open-on-click="true" 
            :close-on-content-click="false"
            class="my-12"
          >

            <template v-slot:activator="{ on }">
              <v-text-field 
                v-model="color_code" 
                :label="$t('general.color')"
                v-on="on" 
                hide-details>

                <template v-slot:prepend>
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
                  :swatches="swatches"
                  show-swatches
                />
              </v-card-text>
              <v-card-actions>
                <v-spacer />
                <v-btn text @click="color_menu = false">{{ $t('general.ok') }}</v-btn>
              </v-card-actions>
            </v-card>
          </v-menu>

          <v-select
            v-model="icon"
            :items="icons"
            label="Icon"
            :prepend-icon="icon"
            class="my-12"
          >

            <template v-slot:item="{ item }">
                <v-list-item-avatar>
                  <v-icon>{{ item }}</v-icon>
                </v-list-item-avatar>

                <v-list-item-content>
                  <v-list-item-title>
                    {{ item }}
                  </v-list-item-title>
                </v-list-item-content>
            </template>

          </v-select>


          <div class="mt-16">
            <v-alert 
              :color="color_code"
              :dismissible="dismissable"
              :outlined="outlined"
              dark
              border="left"
            >
              <template v-slot:prepend>
                <v-icon style="color: inherit !important;">{{ icon }}</v-icon>
              </template>

              <div class="mx-3">
                {{ system_message ? $t(message_text_i18n) : message_text }}
              </div>

              <template v-slot:append v-if="named_route != null && named_route != ''">
                <v-icon style="color: inherit !important;">mdi-open-in-new</v-icon>
              </template>

            </v-alert>
          </div>
        </v-card-text>
      </v-card>
    </v-card-text>

    <v-card-actions>
      <div class="mx-auto">
      <v-btn text @click="resetForm()">{{ $t('general.clear') }}</v-btn>
      <v-btn color="primary" @click="createMessage()">{{ $t('general.create') }}</v-btn>
      </div>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import mtproto from '~/mixins/telegram'

export default {
  name: 'MessageNew',
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper, mtproto],
  components: {
    
  },
  
  data () {
    return {
      color_menu: false,
      date_menu: false,
      system_message: false,
      message_text: null,
      message_text_i18n: null,
      link_text: null,
      named_route: null,
      custom_color: true,
      color_code: '#7E7E7E',
      type: null,
      icon: 'mdi-alert',
      dismissable: true,
      outlined: true,
      show_inbox: true,
      send_telegram: true,
      show_banner: false,
      expires_on: null,
      i18n_strings: [],
      routes: [],
      types: [
        'success',
        'info',
        'error',
        'warning'
      ],
      icons: [
        'mdi-information',
        'mdi-alert',
        'mdi-account',
        'mdi-account-group',
        'mdi-account-convert',
        'mdi-message',
        'mdi-cog',
        'mdi-map-search',
        'mdi-map-marker',
        'mdi-clock',
        'mdi-calendar',
        'mdi-pin',
        'mdi-briefcase',
        'mdi-paperclip',
        'mdi-weather-pouring',
        'mdi-weather-snowy-heavy',
        'mdi-weather-windy',
        'mdi-lightbulb',
        'mdi-shield-alert'
      ],
      swatches: [
        ['#FF1744', '#B71C1C'], // reds
        ['#FF9800', '#EF6C00'], // oranges
        ['#1976D2', '#01579B'], // blues
        ['#4CAF50', '#1B5E20'], // greens
        ['#7E7E7E', '#424242'], // greys
      ],
      ta_rules: [v => v.length <= 2000 || 'Max 2000 characters']
    }
  },

  created () {

    this.getRoutes()
    this.getStrings()

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
    },

    async getStrings() {
      await axios({
        method: 'get',      
        url: '/api/translation/strings/en',
      })
      .then(response => {
          const enStrings = response.data
          const tempStrings = enStrings["system_messages"]

          Object.keys(tempStrings).forEach (key2 => {
            const tempKey = 'system_messages.' + key2
            const tempValue = tempStrings[key2]
            this.i18n_strings.push({"value": tempKey, "text": tempValue});
          })
      })
    },

    async createMessage() {

      // SEND TELEGRAM MESSAGE
      if (this.send_telegram) {
        await this.mtInitialize()

        const channel_id = this.team.notificationsettings.telegram_channel_id
        var message_text = this.system_message ? this.$t(this.message_text_i18n) : this.message_text
        if (this.named_route) {
          var url = this.$router.resolve({ name: this.named_route }).href
          if (url !== '/') {
            message_text += '\n\n' + process.env.MIX_APP_URL + url
          }
        }

        message_text = encodeURIComponent(message_text)
        await this.sendMessage(channel_id, message_text)
      }


      // WRITE TO DATABASE
      await axios({
        method: 'POST',      
        url: '/api/messages',
        data: {
          team_id: this.system_message ? null : this.team.id,
          for_roles: null,
          system_message: this.system_message,
          message_text: this.system_message ? null : this.message_text,
          message_i18n_string: !this.system_message ? null : this.message_text_i18n,
          named_route: this.named_route,
          color: this.custom_color ? this.color_code : null,
          type: this.type,
          icon: this.icon,
          dismissable: this.dismissable,
          outlined: this.outlined,
          show_banner: this.show_banner,
          expires_on: this.expires_on 
        }
      })
      .then(response => {
        this.$emit('updated')
      })
      
    },

    resetForm() {
      this.$emit('clear')
    }
  }
}
</script>