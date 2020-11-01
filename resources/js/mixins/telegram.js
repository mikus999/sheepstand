import { MTProto, getSRPParams } from '@mtproto/core'
import { mapGetters } from 'vuex'

const mtproto = {
  data () {
    return {
      mtproto: null,
      api_id: process.env.MIX_TELEGRAM_API_ID,
      api_hash: process.env.MIX_TELEGRAM_API_HASH,
      bot_token: process.env.MIX_TELEGRAM_BOT_TOKEN,
      bot_id: process.env.MIX_TELEGRAM_BOT_ID,
      bot_name: '@SheepStand_Bot',
      phone_num: null,
      phone_hash: null,
      login_code: null,
      password: null,
      group_name: null,
      group_desc: null,
      stepper: 1,
      error_msg: null,
    }
  },

  computed: {
    ...mapGetters({
      tgUser: 'auth/tgUser',
      tgGroup: 'auth/tgGroup'
    })
  },

  created () {
    this.mtproto = new MTProto({ 
      api_id: this.api_id,
      api_hash: this.api_hash,
      test: false
    })

    this.mtproto.updateInitConnectionParams({
      app_version: '10.0.0',
    })

    console.log(this.mtproto)
  },

  methods: {
    call(method, params, options = {}) {
      return this.mtproto.call(method, params, options)
        .then(result => {
          this.phone_hash = result.phone_code_hash
          return result
        })
        .catch(async error => {
          const { error_code, error_message } = error

          if (error_code === 303) {
            const [type, dcId] = error_message.split('_MIGRATE_')

            // If auth.sendCode call on incorrect DC need change default DC, because call auth.signIn on incorrect DC return PHONE_CODE_EXPIRED error
            if (type === 'PHONE') {
              await this.mtproto.setDefaultDc(+dcId);
            } else {
              options = {
                ...options,
                dcId: +dcId,
              }
            }

            return this.call(method, params, options)
          }

          return Promise.reject(error);
        })
    },

    stepperGo (step) {
      this.error_msg = null
      this.stepper = step
    },

    sendCode() {
      this.call('auth.logOut')
      this.call('auth.sendCode', {
        phone_number: this.phone_num,
        settings: {
          _: 'codeSettings',
        },
      })
      .then(result => {
        this.stepperGo(3)
      })
      .catch(error => {
        this.error_msg = error.error_message
      })
    },

    signIn() {
      this.call('auth.signIn', {
        phone_code: this.login_code,
        phone_number: this.phone_num,
        phone_code_hash: this.phone_hash,
      })
      .then(result => {
        // 2FA is not enabled for this user. User is signed in. Continue to group setup.
        this.$store.dispatch('auth/updateTGUser', result.user)
        this.stepperGo(5)
      })
      .catch(error => {
        // If 2FA is turned on, we must check the password
        if (error.error_message === 'SESSION_PASSWORD_NEEDED') {
          this.error
          this.stepperGo(4)
        } else {
          this.error_msg = error.error_message
        }
  
        return Promise.reject(error);
      })
    },

    signOut () {
      this.error_msg = null
      this.call('auth.logOut')
      this.$store.dispatch('auth/updateTGUser', null)
    },
    
    async checkPassword({ srp_id, A, M1 }) {
      this.call('auth.checkPassword', {
        password: {
          _: 'inputCheckPasswordSRP',
          srp_id,
          A,
          M1,
        },
      })
      .then(result => {
        return Promise.resolve(result)
      })
      .catch(error => {
        return Promise.reject(error)
      })
    },

    async check2FA(password) {
      this.call('account.getPassword').then(async result => {
        const { srp_id, current_algo, srp_B } = result;
        const { g, p, salt1, salt2, } = current_algo;

        await getSRPParams({
          g,
          p,
          salt1,
          salt2,
          gB: srp_B,
          password,
        })
        .then(result => {
          const A = result['A']
          const M1 = result['M1']

          this.call('auth.checkPassword', {
            password: {
              _: 'inputCheckPasswordSRP',
              srp_id,
              A,
              M1,
            },
          })
          .then(result => {
            console.log(result)
            //this.stepperGo(5)
          })
          .catch(error => {
            console.log(error)
            this.error_msg = error.error_message
          })
        })
        .catch(error => {
          this.error_msg = error.error_message
        })


      })
    },


    createSuperGroup () {
      this.call('channels.createChannel', {
        broadcast: false,
        megagroup: true,
        title: 'Test Team Group',
        about: 'Communication from SheepStand'
      })
      .then(result => {
        const group = result.chats[0]
        this.$store.dispatch('auth/updateTGGroup', group)

        this.botAdd()
        this.stepperGo(6)
      })
      .catch(error => {
        this.error_msg = error.error_message
      })
      
    },

    deleteSuperGroup () {
      const channel = {
        _: 'inputChannel',
        channel_id: this.tgGroup.id,
        access_hash: this.tgGroup.access_hash
      };

      this.call('channels.deleteChannel', {
        channel: channel
      })
      .then(result => {
        this.$store.dispatch('auth/updateTGGroup', null)
      })
    },

    async botAdd () {
      const bot = await this.getBotInfo()
      console.log(bot)

      const botUser = {
        _: 'inputUser',
        user_id: bot.id,
        access_hash: bot.access_hash
      }
      const channel = {
        _: 'inputChannel',
        channel_id: this.tgGroup.id,
        access_hash: this.tgGroup.access_hash
      };
     
      this.call('channels.inviteToChannel', {
        channel: channel,
        users: [botUser]
      })
      .then(update => {
        console.log(update)
      })
    },

    async getBotInfo () {
      return new Promise((resolve, reject) => {
        this.call('contacts.search', {
          q: this.bot_name,
          limit: 10
        })
        .then(result => {
          const bot = result.users[0]
          resolve(bot)
        })
      })
    },

  }
}

export default mtproto