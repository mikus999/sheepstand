import Vue from 'vue'
import Vuetify from 'vuetify'
import 'vuetify/dist/vuetify.min.css'

Vue.use(Vuetify)

export default new Vuetify({
  theme: {
    dark: false,
    themes: {
      light: {
        primary: '#607d8b',
        secondary: '#ff5722',
        accent: '#ff5722',
        error: '#f44336'
      }
    }
  }
})
