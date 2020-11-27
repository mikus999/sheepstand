<template>
<v-btn v-if="googleAuth" class="my-3" color="#ffffff" @click="login" block>
  <!--<v-icon small>mdi-google</v-icon>-->
  <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
    <path style="fill:#FBBB00;" d="M113.47,309.408L95.648,375.94l-65.139,1.378C11.042,341.211,0,299.9,0,256
          c0-42.451,10.324-82.483,28.624-117.732h0.014l57.992,10.632l25.404,57.644c-5.317,15.501-8.215,32.141-8.215,49.456
          C103.821,274.792,107.225,292.797,113.47,309.408z" />
    <path style="fill:#518EF8;" d="M507.527,208.176C510.467,223.662,512,239.655,512,256c0,18.328-1.927,36.206-5.598,53.451
          c-12.462,58.683-45.025,109.925-90.134,146.187l-0.014-0.014l-73.044-3.727l-10.338-64.535
          c29.932-17.554,53.324-45.025,65.646-77.911h-136.89V208.176h138.887L507.527,208.176L507.527,208.176z" />
    <path style="fill:#28B446;" d="M416.253,455.624l0.014,0.014C372.396,490.901,316.666,512,256,512
          c-97.491,0-182.252-54.491-225.491-134.681l82.961-67.91c21.619,57.698,77.278,98.771,142.53,98.771
          c28.047,0,54.323-7.582,76.87-20.818L416.253,455.624z" />
    <path style="fill:#F14336;" d="M419.404,58.936l-82.933,67.896c-23.335-14.586-50.919-23.012-80.471-23.012
          c-66.729,0-123.429,42.957-143.965,102.724l-83.397-68.276h-0.014C71.23,56.123,157.06,0,256,0
          C318.115,0,375.068,22.126,419.404,58.936z" />
  </svg>

  <span class="pl-4 black--text font-weight-bold">Sign In with Google</span>
</v-btn>
</template>

<script>
export default {
  name: 'LoginWithGoogle',

  data() {
    return {
      googleAuth: process.env.MIX_GOOGLE_CLIENT_ID
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
        provider: 'google'
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
