<template>
<v-btn class="ma-2 pa-2" :large="$vuetify.breakpoint.smAndUp" :href="dlLink" target="_blank">
  <v-icon :large="$vuetify.breakpoint.smAndUp">{{ osDetails.icon }}</v-icon>
  <span class="pl-2">Download for </span>
  <span class="pl-1"><b>{{ osDetails.name }}</b></span>
</v-btn>
</template>

<script>
import helper from '~/mixins/helper'

export default {
  name: 'DownloadTelegram',
  mixins: [helper],
  data() {
    return {
      osDetails: {},
      dlLink: null
    }
  },

  created() {
    this.osDetails = this.getOS()
    this.dlLink = this.getTelegramLink(this.osDetails.name)
  },

}
</script>
