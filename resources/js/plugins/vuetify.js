import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import 'vuetify/dist/vuetify.min.css'
import colors from 'vuetify/lib/util/colors'

Vue.use(Vuetify)

export default new Vuetify({
  theme: {
    treeshaking: true,
    dark: true,
    themes: {
      light: {
        background: colors.white
      },
      dark: {
        primary: colors.lightBlue.darken2,
        secondary: colors.lightBlue.darken4,
        accent: colors.lightBlue.accent4,
        background: colors.grey.darken4
      },
    },
    options: {
      customProperties: true,
    },
  }
})
