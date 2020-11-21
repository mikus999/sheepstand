import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import 'vuetify/dist/vuetify.min.css'
import colors from 'vuetify/lib/util/colors'

Vue.use(Vuetify)

export default new Vuetify({
  theme: {
    treeshaking: true,
    themes: {
      light: {
        primary: colors.lightBlue.darken2,
        anchor: colors.lightBlue.darken4,
        background: colors.white
      },
      dark: {
        primary: colors.lightBlue.darken2,
        secondary: colors.lightBlue.darken4,
        anchor: colors.lightBlue.lighten3,
        accent: colors.lightBlue.accent4,
        background: colors.grey.darken4
      },
    },
    options: {
      customProperties: true,
    },
  }
})
