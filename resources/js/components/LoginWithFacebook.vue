<template>
<v-btn v-if="facebookAuth" class="my-3" color="#385298" @click="login" block>
<svg
   xmlns="http://www.w3.org/2000/svg"
   version="1.1"
   id="svg2"
   xml:space="preserve"
   width="25"
   height="25"
   viewBox="0 0 1365.3333 1365.3333"
   ><defs
     id="defs6" /><sodipodi:namedview
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1"
     objecttolerance="10"
     gridtolerance="10"
     guidetolerance="10"
     inkscape:pageopacity="0"
     inkscape:pageshadow="2"
     inkscape:window-width="640"
     inkscape:window-height="480"
     id="namedview4" /><g
     id="g10"
     inkscape:groupmode="layer"
     inkscape:label="ink_ext_XXXXXX"
     transform="matrix(1.3333333,0,0,-1.3333333,0,1365.3333)"><g
       id="g12"
       transform="scale(0.1)"><path
         d="m 10240,5120 c 0,2827.7 -2292.3,5120 -5120,5120 C 2292.3,10240 0,7947.7 0,5120 0,2564.46 1872.31,446.301 4320,62.1992 V 3640 H 3020 v 1480 h 1300 v 1128 c 0,1283.2 764.38,1992 1933.9,1992 560.17,0 1146.1,-100 1146.1,-100 V 6880 H 6754.38 C 6118.35,6880 5920,6485.33 5920,6080.43 V 5120 H 7340 L 7113,3640 H 5920 V 62.1992 C 8367.69,446.301 10240,2564.46 10240,5120"
         style="fill:#ffffff;fill-opacity:1;fill-rule:nonzero;stroke:none"
         id="path14" /></g></g></svg>

  <span class="pl-4 white--text font-weight-bold">Login with Facebook</span>
</v-btn>
</template>

<script>
export default {
  name: 'LoginWithFacebook',

  data() {
    return {
      facebookAuth: process.env.MIX_FACEBOOK_APP_ID
    }
  },

  mounted() {
    window.addEventListener('message', this.onMessage, false)
  },

  beforeDestroy() {
    window.removeEventListener('message', this.onMessage)
  },

  methods: {
    async login() {
      const newWindow = openWindow('', this.$t('auth.login'))

      const url = await this.$store.dispatch('auth/fetchOauthUrl', {
        provider: 'facebook'
      })

      newWindow.location.href = url
    },

    /**
     * @param {MessageEvent} e
     */
    onMessage(e) {
      if (e.origin !== window.origin || !e.data.token) {
        return
      }

      this.$store.dispatch('auth/saveToken', {
        token: e.data.token
      })

      // Redirect home when store is initiated
      this.$store.dispatch('general/init').then(() => {
        this.$router.push({
          name: 'home'
        })
      })

    }
  }
}

/**
 * @param  {Object} options
 * @return {Window}
 */
function openWindow(url, title, options = {}) {
  if (typeof url === 'object') {
    options = url
    url = ''
  }

  options = {
    url,
    title,
    width: 600,
    height: 720,
    ...options
  }

  const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screen.left
  const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screen.top
  const width = window.innerWidth || document.documentElement.clientWidth || window.screen.width
  const height = window.innerHeight || document.documentElement.clientHeight || window.screen.height

  options.left = ((width / 2) - (options.width / 2)) + dualScreenLeft
  options.top = ((height / 2) - (options.height / 2)) + dualScreenTop

  const optionsStr = Object.keys(options).reduce((acc, key) => {
    acc.push(`${key}=${options[key]}`)
    return acc
  }, []).join(',')

  const newWindow = window.open(url, title, optionsStr)

  if (window.focus) {
    newWindow.focus()
  }

  return newWindow
}
</script>
