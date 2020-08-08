import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)
Vue.config.devtools = true

/*
import auth from './modules/auth';
import lang from './modules/lang';
import teams from './modules/teams';

export default new Vuex.Store({
  modules: {
    auth: auth,
    lang: lang,
    teams: teams
  }
})
*/

// Load store modules dynamically.
const requireContext = require.context('./modules', false, /.*\.js$/)

const modules = requireContext.keys()
  .map(file =>
    [file.replace(/(^.\/)|(\.js$)/g, ''), requireContext(file)]
  )
  .reduce((modules, [name, module]) => {
    if (module.namespaced === undefined) {
      module.namespaced = true
    }

    return { ...modules, [name]: module }
  }, {})

export default new Vuex.Store({
  modules
})
