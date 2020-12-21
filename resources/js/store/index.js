import Vue from 'vue'
import Vuex from 'vuex'
import persistedState from "vuex-persistedstate";
import SecureLS from "secure-ls";
var ls = new SecureLS({ isCompression: false });

Vue.use(Vuex)
Vue.config.devtools = true


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

  const encryptStorage = {
    getItem: (key) => ls.get(key),
    setItem: (key, value) => ls.set(key, value),
    removeItem: (key) => ls.remove(key),
  }

export default new Vuex.Store({
  modules,
  plugins: [
    persistedState(
      { 
        key: 'vuex_auth',
        paths: [
          "auth",
        ],
        storage: process.env.NODE_ENV != 'production' ? '' : encryptStorage
      }
    ),
    persistedState(
      { 
        key: 'vuex_public',
        paths: [
          "general",
          "route",
          "lang",
          "snackbar"
        ],
        storage: process.env.NODE_ENV != 'production' ? '' : encryptStorage
      }
    ),
    persistedState(
      { 
        key: 'vuex_schedule',
        paths: [
          "scheduling",
        ],
        storage: process.env.NODE_ENV != 'production' ? '' : encryptStorage
      }
    ),    
  ],
})
