<template>
  <v-card>
    <v-card-title>
      {{ $t('messages.inbox')}}
    </v-card-title>

    <v-card-text>
      <v-list flat>
        <v-list-item-group>
          <template v-for="(message, index) in messages">
            <v-list-item
              :key="message.id"
              @click="goToRoute(message.named_route)"
            >
              <v-list-item-icon>
                <v-icon 
                  v-text="getMessageType(message).icon"
                  :color="getMessageType(message).color"
                  ></v-icon>
              </v-list-item-icon>

              <v-list-item-content>
                <v-list-item-title>
                  {{ message.system_message ? $t(message.message_i18n_string) : message.message_text }}
                </v-list-item-title>
              </v-list-item-content>
            </v-list-item>

            <v-divider :key="'div-'+message.id" v-if="index < messages.length - 1" />
          </template>
        </v-list-item-group>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'MessageList',
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    
  },
  
  data () {
    return {
      messages: {},
      message_types: {
        success: {
          icon: 'mdi-information',
          color: 'green',
        },
        info: {
          icon: 'mdi-information',
          color: 'primary',
        },
        error: {
          icon: 'mdi-alert',
          color: 'red',
        },
        warning: {
          icon: 'mdi-information',
          color: 'orange',
        },        
      },
    }
  },

  computed: {

  },

  created () {
    this.getMessages()
  },

  methods: {
    async getMessages () {
      await axios({
        method: 'get',      
        url: '/api/messages',
      })
      .then(response => {
        this.messages = response.data.messages
      })
    },

    goToRoute (named_route) {
      if (named_route != null) {
        this.$router.push({ name: named_route })
      }
    },

    getMessageType (message) {
      var icon = message.icon
      var color = message.color

      var temp = this.message_types[message.type]

      if (message.icon != null) {
        temp.icon = message.icon
      }
      if (message.color != null) {
        temp.color = message.color
      }
      
      return temp
    }
  }
  
}
</script>