<template>
  <v-dialog v-model="dialog" max-width="500" @keydown.esc="cancel">
    <v-card>
      <v-toolbar dark :color="color" flat>
        <v-toolbar-title class="white--text">{{ title }}</v-toolbar-title>
      </v-toolbar>
      <v-card-text v-show="!!message" class="pa-4 body-1">
        <span v-html="message"></span>
      </v-card-text>
      <v-card-actions class="pt-0">
        <v-spacer></v-spacer>
        <v-btn text @click.native="cancel">{{ $t('general.cancel') }}</v-btn>
        <v-btn color="primary" @click.native="agree">{{ $t('general.ok') }}</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
/**
 * if (await this.$root.$confirm(title, message, color)) {
 *   // yes
 * }
 * else {
 *   // cancel
 * }
 *
 *   this.$root.$confirm = this.$refs.confirm.open
 */


export default {
  name: 'ConfirmBox',

  data () {
    return {
      dialog: false,
      resolve: null,
      reject: null,
      message: null,
      title: null,
      color: null,
    }
  },
  
  methods: {
    open(message, title, color) {
      this.dialog = true
      this.message = message
      this.title = title ? title : this.$t('general.confirm')
      this.color = color ? color : 'grey darken-2'

      return new Promise((resolve, reject) => {
        this.resolve = resolve
        this.reject = reject
      })
    },

    agree() {
      this.resolve(true)
      this.dialog = false
    },

    cancel() {
      this.resolve(false)
      this.dialog = false
    }
  }
}
</script>