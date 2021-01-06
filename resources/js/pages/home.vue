<template>
  <v-container fluid>
    <v-row v-if="banners.length > 0">
      <MessageBanner v-for="banner in banners" :message="banner" :key="banner.id" />
    </v-row>

    <v-row v-if="user">
      <MyShifts />

      <v-col><v-spacer class="my-12" /></v-col>

      <TradeRequests />
    </v-row>

  </v-container>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import TradeRequests from '~/components/TradeRequests.vue'
import MyShifts from '~/components/MyShifts.vue'
import NotificationJoin from '~/components/NotificationJoin.vue'
import MessageBanner from '~/components/MessageBanner.vue'


export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],
  components: {
    TradeRequests,
    MyShifts,
    NotificationJoin,
    MessageBanner
  },

  data() {
    return {
      banners: []
    }
  },

  created() {
    this.getBanners()
  },

  methods: {
    async getBanners() {
      await axios.get('/api/messages/banners')
      .then(response => {
        this.banners = response.data.data.banners
      })
    }
  },
}
</script>
