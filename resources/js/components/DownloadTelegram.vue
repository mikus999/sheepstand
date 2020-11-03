<template>
<v-btn class="m-2" large :href="dlLink" target="_blank">
  <v-icon large>{{ osDetails.icon }}</v-icon>
  <span class="pl-3">Download for </span>
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
