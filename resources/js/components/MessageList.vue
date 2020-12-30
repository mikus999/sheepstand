<template>
  <v-card width="100%" :flat="flat">
    <v-toolbar flat>
      <v-toolbar-title v-if="showTitle">
        <v-icon left>mdi-message</v-icon>
        {{ $t('messages.inbox')}}
      </v-toolbar-title>

      <v-spacer></v-spacer>
      
      <template v-slot:extension>
        <v-switch v-model="allMessages" hide-details class="mx-4">
          <template v-slot:label>
            <span class="switch-label">{{ $t('messages.show_expired_messages') }}</span>
          </template>
        </v-switch>
      </template>
    </v-toolbar>

    <v-card-text class="px-0">
      <v-tabs v-model="message_type" icons-and-text grow class="tab-links">
        <v-tab>{{ $t('messages.received') }}</v-tab>
        <v-tab>{{ $t('messages.sent') }}</v-tab>
      </v-tabs>

      <v-list flat>
        <v-list-item-group>
          <v-divider />
          <template v-for="(message, index) in filteredMessages">
            <v-list-item :key="message.id" @click.native="showMessage(message)">
              <v-list-item-avatar>
                <v-icon v-if="isExpired(message.expires_on)">mdi-email-off</v-icon>
                <v-icon v-else>{{ isUnread(message) ? 'mdi-email' : 'mdi-email-open' }}</v-icon>
              </v-list-item-avatar>

              <v-list-item-content 
                :class="isUnread(message) ? 'font-weight-black' : ''">
                  <v-list-item-title>
                    <span v-if="message_type == 1">{{ $t('messages.sent_to') }}: </span>
                    <span>{{ message_type == 0 ? getSenderName(message) : getRecipientName(message) }}</span>
                    <span class="red--text text-overline ml-3" v-if="isExpired(message.expires_on)">{{ $t('general.expired') }}</span>
                  </v-list-item-title>

                  <v-list-item-subtitle>
                    {{ message.system_message ? $t(message.message_i18n_string) : message.message_text }}
                  </v-list-item-subtitle>

              </v-list-item-content>

              <v-list-item-action>
                <v-list-item-action-text class="mb-auto">
                  {{ message.created_at | formatDate }}
                </v-list-item-action-text>

                <div class="my-auto">
                  <v-btn icon v-if="message.named_route" :to="{ name: message.named_route }" @click.stop class="mb-n2">
                    <v-icon>mdi-link</v-icon>
                  </v-btn>

                  <v-btn icon v-if="editor" @click.stop="deleteMessage(message.id)">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </div>
              </v-list-item-action>

            </v-list-item>

            <v-divider :key="'div-'+message.id" v-if="index < received.length - 1" />
      
          </template>
          
        </v-list-item-group>
      </v-list>
    </v-card-text>


    <v-dialog v-model="messageDialog" width="500">
      <MessageView 
        :key="message ? message.id : 1"
        :message="message" 
        v-on:close="closeMessage" 
      />
    </v-dialog>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import MessageView from '~/components/MessageView.vue'

export default {
  name: 'MessageList',
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    MessageView
  },

  props: {
    editor: {
      type: Boolean,
      default: false
    },
    showTitle: {
      type: Boolean,
      default: false
    },
    flat: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      received: [],
      sent: [],
      message: null,
      messageDialog: false,
      allMessages: false,
      base_url: process.env.MIX_APP_URL,
      apiInterval: null,
      message_type: 0
    }
  },

  computed: {
    filteredMessages() {
      if (this.allMessages) {
        return this.message_type == 0 ? this.received : this.sent
      } else {
        var messages = this.message_type == 0 ? this.received : this.sent
        return messages.filter(message => !this.isExpired(message.expires_on))
      }
    }
  },

  created () {
    this.getMessages()
    this.apiInterval = window.setInterval(() => {
      this.getMessages()
    }, 60000)
  },

  beforeDestroy() {
    window.clearInterval(this.apiInterval)
  },

  methods: {
    async getMessages () {
      await axios({
        method: 'get',      
        url: '/api/messages',
      })
      .then(response => {
        this.received = response.data.received.filter(message => 
                          message.sender_type != 'App\\Models\\User' ||
                          (message.sender_type == 'App\\Models\\User' && 
                          message.sender_id != this.user.id))

        this.sent = response.data.sent
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
          this.received = response.data.received
          this.sent = response.data.sent
        })
      }
    },

    showMessage (message) {
      this.message = message
      this.messageDialog = true
    },

    closeMessage() {
      this.messageDialog = false
      this.getMessages()
    },

    goToRoute (named_route) {
      if (named_route != null) {
        this.$router.push({ name: named_route })
      }
    },

    getSenderName(message) {
      if (message.sender_type == null) {
        return 'SheepStand'
      } else if (message.sender_type == 'App\\Models\\Team') {
        return message.sender.display_name + ' (' + this.$t('general.team') + ')'
      } else if (message.sender_type == 'App\\Models\\User') {
        return message.sender.name
      }
    },

    getRecipientName(message) {
      if (message.recipient_type == null) {
        return 'SheepStand'
      } else if (message.recipient_type == 'App\\Models\\Team') {
        return message.recipient.display_name + ' (' + this.$t('general.team') + ')'
      } else if (message.recipient_type == 'App\\Models\\User') {
        return message.recipient.name
      }
    },

    isExpired(expired_on) {
      if (expired_on != null) {
        return this.$dayjs(expired_on).isBefore(this.$dayjs())
      } else {
        return false
      }
    },

    isUnread(message) {
      var unread = message.users.length == 0
      return this.message_type == 0 ? unread : false
    }

  }
  
}
</script>

<style scoped>
  .word-wrap 
  {
    -webkit-line-clamp: unset !important;
    white-space: normal;
  }

  .switch-label
  {
    font-size: .85rem !important;
  }

  .tab-links a {
    text-decoration: none;
  }

</style>