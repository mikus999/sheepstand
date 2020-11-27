<template>
  <div id="app">
    <loading ref="loading" />

    <transition name="page" mode="out-in">
      <component :is="layout" v-if="layout" />
    </transition>
  </div>
</template>

<script>
import Loading from './Loading'

// Load layout components dynamically.
const requireContext = require.context('~/layouts', false, /.*\.vue$/)

const layouts = requireContext.keys()
  .map(file =>
    [file.replace(/(^.\/)|(\.vue$)/g, ''), requireContext(file)]
  )
  .reduce((components, [name, component]) => {
    components[name] = component.default || component
    return components
  }, {})

export default {
  el: '#app',

  components: {
    Loading
  },

  data: () => ({
    layout: null,
    defaultLayout: 'vuetify',
    apiInterval: null
  }),

  metaInfo () {
    return {
      title: 'SheepStand',
    }
  },

  mounted () {
    this.$loading = this.$refs.loading

    // Get/setinitial dark/light mode
    this.$store.dispatch('general/getTheme')
  },

  created () {
    this.scheduledTasks()
  },

  methods: {
    /**
     * Set the application layout.
     *
     * @param {String} layout
     */
    setLayout (layout) {
      if (!layout || !layouts[layout]) {
        layout = this.defaultLayout
      }

      this.layout = layouts[layout]
    },


    scheduledTasks() {
      // Runs scheduled tasks once per minute
      this.apiInterval = window.setInterval(() => {
        this.$store.dispatch('general/scheduledTasks')
      }, 60000)
    }

  }
}
</script>
