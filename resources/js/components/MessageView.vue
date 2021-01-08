<template>
  <v-card>
    <v-card-title color="grey lighten-2">
      {{ senderName }}
    </v-card-title>

    <v-card-subtitle color="grey lighten-2" class="py-2">
      <div>
        <span>{{ $t('messages.sent_to') }}: </span>
        <span class="font-weight-bold">{{ recipientName }}</span>
      </div>
      <div>
        <span>{{ $t('messages.sent_date') }}: </span>
        <span class="font-weight-bold">{{ $dayjs(message.created_at).format('llll') }}</span>
      </div>

      <div class="mt-2">
        <span class="black--text font-weight-bold">{{ message.message_subject }}</span>
      </div>
    </v-card-subtitle>

    <v-card-text class="py-2 my-2 overflow-auto" style="height: 300px;">
      <span class="message-body">{{ message.message_body }}</span>
    </v-card-text>

    <v-divider></v-divider>

    <v-card-actions>
      <v-btn icon v-if="message.named_route" :to="{ name: message.named_route }" class="mb-n2">
        <v-icon>{{ icons.mdiLink }}</v-icon>
      </v-btn>

      <v-spacer></v-spacer>
      <!--<v-btn text color="red" @click="deleteMessage(message.id)">
        {{ $t('general.delete') }}
      </v-btn>-->
      <v-btn color="primary" @click="$emit('close')">
        {{ $t('general.close') }}
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: "MessageView",
  mixins: [helper],
  props: {
    message: {
      type: [Array, Object],
      required: true
    }
  },

  data() {
    return {
      
    }
  },

  computed: {
    isSent() {
      return (this.message.sender_type == 'App\\Models\\User' && this.message.sender_id == this.user.id)
    },

    senderName() {
      if (this.message.sender_type == null) {
        return 'SheepStand'
      } else if (this.message.sender_type == 'App\\Models\\Team') {
        return this.message.sender.display_name + ' (' + this.$t('general.team') + ')'
      } else if (this.message.sender_type == 'App\\Models\\User') {
        return this.message.sender.name
      }
    },

    recipientName() {
      if (this.message.recipient_type == null) {
        return this.$t('messages.to_everyone')
      } else if (this.message.recipient_type == 'App\\Models\\Team') {
        return this.message.recipient.display_name + ' (' + this.$t('general.team') + ')'
      } else if (this.message.recipient_type == 'App\\Models\\User') {
        return this.message.recipient.name
      }
    }
  },

  created() {
    this.markAsRead()
  },

  methods: {
    
    async deleteMessage(id) {
      if (await this.$root.$confirm(this.$t('messages.confirm_delete_message'), null, 'error')) {
        await axios({
          method: 'delete',      
          url: '/api/messages/' + id,
        })
        .then(response => {
          this.showSnackbar(this.$t('messages.success_delete_message'), 'success')
          this.$emit('close')
        })
      }
    },


    async markAsRead () {
      await axios({
        method: 'get',      
        url: '/api/messages/' + this.message.id + '/markread',
      })
      .then(response => {    
        this.getMessageCounts()    
      })
    },

    goToRoute (named_route) {
      if (named_route != null) {
        this.$router.push({ name: named_route })
      }
    },

  },
}
</script>


<style scoped>
.message-body {
  white-space: pre-wrap; 
  word-wrap: break-word;
  font-family: inherit;
}
</style>