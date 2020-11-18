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
              @click="editor ? '' : goToRoute(message.named_route)"
            >

              <v-list-item-icon>
                <v-icon 
                  v-text="message.icon"
                  :color="message.color"
                  ></v-icon>
              </v-list-item-icon>

              <v-list-item-content>
                <v-list-item-title :style="'color: ' + message.color">
                  {{ message.system_message ? $t(message.message_i18n_string) : message.message_text }}
                </v-list-item-title>
              </v-list-item-content>

              <v-list-item-action v-if="editor">
                <v-icon
                  @click="deleteMessage(message.id)"
                >mdi-delete</v-icon>
              </v-list-item-action>

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

  props: {
    editor: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      messages: []
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

    async deleteMessage(id) {
      if (await this.$root.$confirm(this.$t('messages.confirm_delete_message'), null, 'error')) {
        await axios({
          method: 'delete',      
          url: '/api/messages/' + id,
        })
        .then(response => {
          this.showSnackbar(this.$t('messages.success_delete_message'), 'success')
          this.messages = response.data.messages
        })
      }
    },

    goToRoute (named_route) {
      if (named_route != null) {
        this.$router.push({ name: named_route })
      }
    },

  }
  
}
</script>
