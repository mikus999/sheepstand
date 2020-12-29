<template>
  <v-card>
    <v-card-title color="grey lighten-2">
      {{ getRecipientName() }}
    </v-card-title>

    <v-card-subtitle color="grey lighten-2" class="py-2">
      {{ message.created_at | formatDate }}
    </v-card-subtitle>

    <v-card-text class="py-6 my-2 overflow-auto" style="height: 300px;">
      {{ message.system_message ? $t(message.message_i18n_string) : message.message_text }}
    </v-card-text>

    <v-divider></v-divider>

    <v-card-actions>
      <v-btn icon v-if="message.named_route" :to="{ name: message.named_route }" class="mb-n2">
        <v-icon>mdi-link</v-icon>
      </v-btn>

      <v-spacer></v-spacer>
      <v-btn text color="red" @click="deleteMessage(message.id)">
        {{ $t('general.delete') }}
      </v-btn>
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

    getRecipientName() {
      if (this.message.recipient_type == null) {
        return 'SheepStand'
      } else if (this.message.recipient_type == 'App\\Models\\Team') {
        return this.message.recipient.display_name
      } else if (this.message.recipient_type == 'App\\Models\\User') {
        return 'SheepStand'
      }
    }

  },
}
</script>