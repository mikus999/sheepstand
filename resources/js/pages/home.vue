<template>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  middleware: 'auth',
  layout: 'vuetify',

  components: {
  },

  data: () => ({
  }),

  metaInfo () {
    return { title: this.$t('home') }
  },

  computed: {
  },

  created () {
  },

  mounted () {
  },

  methods: {
  }

}
</script>
