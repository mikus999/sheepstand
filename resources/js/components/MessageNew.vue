<template>
  <v-card>
    <v-card-title>
      <v-icon left>{{ icons.mdiMessagePlus }}</v-icon>
      {{ $t('messages.create_new_message')}}
    </v-card-title>

    <v-card-text>
      <v-row>
        <v-col cols=12 sm=6>

          <v-radio-group
            v-model="sender_type"
            :label="$t('messages.sent_from')"
            column
            >
            <v-radio :label="user.name" value="User"></v-radio>
            <v-radio :label="team.display_name" value="Team"></v-radio>
            <v-radio label="Site Administrators" value="Site" v-if="$is('super_admin')"></v-radio>
          </v-radio-group>
        </v-col>
        <v-col cols=12 sm=6>
          <v-radio-group
            v-model="recipient_type"
            :label="$t('messages.sent_to')"
            column>
            <v-radio :label="$t('messages.to_all_team_members')" value="Team" v-if="sender_type != 'Site'"></v-radio>
            <v-radio label="All Users" value="Site" v-if="$is('super_admin') && sender_type == 'Site'"></v-radio>
          </v-radio-group>
        </v-col>
      </v-row>

      <v-textarea 
        v-model="message_subject" 
        :label="$t('messages.message_subject')"
        :hint="$t('general.max_char', {char: 100})"
        maxlength="100"
        counter
        auto-grow
        rows=1 />

      <v-textarea 
        v-model="message_body" 
        :label="$t('messages.message_body')"
        auto-grow
        rows=2 />

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
            :prepend-icon="icons.mdiCalendar"
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


      <v-switch v-model="show_inbox" :label="$t('messages.show_in_inbox')" readonly />
      <v-switch v-model="send_telegram" :label="$t('messages.send_to_telegram')" :disabled="!notificationsEnabled" />
      <v-switch v-model="show_banner" :label="$t('messages.show_as_banner')" />
      
      <v-card v-if="show_banner" outlined class="pa-8">
        <v-card-title>
          {{ $t('messages.banner_options')}}
        </v-card-title>

        <v-card-text>

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
                hide-details
                :clearable="false"
              >

                <template v-slot:prepend>
                  <v-icon :color="color_code">{{ icons.mdiSquareRounded }}</v-icon>
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


          <div class="mt-16">
            <v-col class="pa-0">{{ $t('general.icon')}}</v-col>
            <v-col class="pa-0">
              <v-icon 
                v-for="i in bannerIcons" 
                :key="i" 
                @click="icon = i"
                :color="icon == i ? 'primary' : ''"
                class="mr-2 mb-2"
              >
                {{ i }}
              </v-icon>
            </v-col>
          </div>


          <div class="mt-16">
            <v-alert 
              :color="color_code"
              border="left"
              outlined
              text
              dismissible
              dense
            >
              <template v-slot:prepend>
                <v-icon style="color: inherit !important;">{{ icon }}</v-icon>
              </template>

              <div class="mx-3">
                {{ message_subject }}
              </div>

              <template v-slot:append v-if="named_route != null && named_route != ''">
                <v-icon style="color: inherit !important;">{{ icons.mdiOpenInNew }}</v-icon>
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
  layout: 'sidebar',
  mixins: [helper, mtproto],
  components: {
    
  },
  
  data () {
    return {
      color_menu: false,
      date_menu: false,
      sender_type: 'User',
      recipient_type: 'Team',
      message_subject: null,
      message_body: null,
      named_route: null,
      custom_color: true,
      color_code: '#7E7E7E',
      icon: null,
      show_inbox: true,
      send_telegram: false,
      show_banner: false,
      expires_on: null,
      routes: [],
      types: [
        'success',
        'info',
        'error',
        'warning'
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
    this.icon = this.icons.mdiInformation
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
    },

    async createMessage() {
      var expires_date = this.expires_on
      if (expires_date != null) {
        // Set the expire date to midnight the same day
        expires_date = this.$dayjs(this.expires_on).format('YYYY-MM-DD') + ' 23:59:59'
      }



      // SEND TELEGRAM MESSAGE
      if (this.send_telegram && this.team.notificationsettings) {
        await this.mtInitialize()

        const channel_id = this.team.notificationsettings.telegram_channel_id
        var message_text = this.message_subject

        if (this.message_body) {
          message_text += '\n\n' + this.message_body
        }

        if (this.named_route) {
          var url = this.$router.resolve({ name: this.named_route }).href
          if (url !== '/') {
            message_text += '\n\n' + process.env.MIX_APP_URL + url
          }
        }

        await this.sendMessage(channel_id, message_text)
      }


      // WRITE TO DATABASE
      await axios({
        method: 'POST',      
        url: '/api/messages',
        data: {
          sender_id: this.getSenderId(this.sender_type),
          sender_type: this.getModelType(this.sender_type),
          recipient_id: this.getRecipientId(this.recipient_type),
          recipient_type: this.getModelType(this.recipient_type),
          message_subject: this.message_subject,
          message_body: this.message_body,
          named_route: this.named_route,
          color: this.custom_color ? this.color_code : null,
          icon: this.icon,
          show_banner: this.show_banner,
          expires_on: expires_date 
        }
      })
      .then(response => {
        this.$emit('updated')
      })
      
    },

    getSenderId (sender_type) {
      var value = null
      if (sender_type == 'Site') {
        value = null
      } else if (sender_type == 'Team') {
        value = this.team.id
      } else {
        value = this.user.id
      }
      return value
    },

    getRecipientId (recipient_type) {
      var value = null
      if (this.sender_type == 'Site') {
        value = null // Send to all users
      } else if (recipient_type == 'Team') {
        value = this.team.id
      } else {
        value = this.user.id
      }
      return value
    },

    getModelType(model_type) {
      var value = null
      if (this.sender_type == 'Site') {
        value = null
      } else if (model_type == 'Team') {
        value = 'App\\Models\\Team'
      } else {
        value = 'App\\Models\\User'
      }
      return value
    },

    resetForm() {
      this.$emit('clear')
    }
  }
}
</script>