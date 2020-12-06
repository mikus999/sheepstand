<template>
  <v-snackbar v-model="show" :timeout="3000" :color="color" :top="$vuetify.breakpoint.xs">
    {{ message }}
    <v-btn text @click="show = false">{{ $t('general.close') }}</v-btn>
  </v-snackbar>
</template>

<script>
export default {
  name: 'Snackbar',
  data () {
    return {
      show: false,
      message: '',
      color: ''
    }
  },

  created () {
    this.$store.subscribe((mutation, state) => {
      if (mutation.type === 'snackbar/SHOW_MESSAGE') {
        this.message = state.snackbar.content
        this.color = state.snackbar.color
        this.show = true
      }
    })
  }
}
</script>