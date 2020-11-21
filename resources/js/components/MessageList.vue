<template>
  <v-card>
    <v-card-title v-if="showTitle">
      {{ $t('messages.inbox')}}
    </v-card-title>

    <v-card-text>
      <v-list flat>
        <v-list-item-group>
          <v-divider />
          <template v-for="(message, index) in messages">
            <v-list-item :key="message.id" @click.native="markAsRead(message.id)">

              <v-list-item-avatar>
                <v-icon>{{ message.users.length > 0 ? 'mdi-email-open' : 'mdi-email' }}</v-icon>
              </v-list-item-avatar>

              <v-list-item-content 
                :class="message.users.length > 0 ? '' : 'font-weight-black'">
                  <v-list-item-title>
                    {{ message.system_message ? 'SheepStand' : message.team.display_name}}
                  </v-list-item-title>

                  <v-list-item-subtitle class="word-wrap">
                    {{ message.system_message ? $t(message.message_i18n_string) : message.message_text }}
                  </v-list-item-subtitle>

              </v-list-item-content>

              <v-list-item-action>
                <v-list-item-action-text class="mb-auto">
                  {{ message.created_at | formatDate }}
                </v-list-item-action-text>

                <div class="my-auto">
                  <v-btn icon v-if="message.named_route" :to="{ name: message.named_route }" class="mb-n2">
                    <v-icon>mdi-link</v-icon>
                  </v-btn>

                  <v-btn icon v-if="editor" @click="deleteMessage(message.id)">
                    <v-icon>mdi-delete</v-icon>
                  </v-btn>
                </div>
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
    },
    showTitle: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      messages: [],
      base_url: process.env.MIX_APP_URL,
      apiInterval: null
    }
  },

  computed: {

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

    async markAsRead (id) {
      await axios({
        method: 'get',      
        url: '/api/messages/' + id + '/markread',
      })
      .then(response => {
        this.messages = response.data.messages
        
        this.$store.dispatch('general/scheduledTasks')
      })
    },

    goToRoute (named_route) {
      if (named_route != null) {
        this.$router.push({ name: named_route })
      }
    },

  }
  
}
</script>

<style scoped>
.word-wrap 
{
  -webkit-line-clamp: unset !important;
  white-space: normal;
}
</style>