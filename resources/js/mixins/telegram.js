import { MTProto, getSRPParams } from '@mtproto/core'
import { mapGetters } from 'vuex'
import axios from 'axios'

export const mtproto = {
  data () {
    return {
      mtproto: null,
      api_id: process.env.MIX_TELEGRAM_API_ID,
      api_hash: process.env.MIX_TELEGRAM_API_HASH,
      bot_token: process.env.MIX_TELEGRAM_BOT_TOKEN,
      bot_id: process.env.MIX_TELEGRAM_BOT_ID,
      bot_name: '@SheepStand_Bot',
      bot_api_base: 'https://api.telegram.org/bot' + process.env.MIX_TELEGRAM_BOT_TOKEN + '/',
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

  },

  methods: {
    async mtInitialize () {

      this.mtproto = new MTProto({ 
        api_id: this.api_id,
        api_hash: this.api_hash,
        test: false
      })
  
      this.mtproto.updateInitConnectionParams({
        app_version: '10.0.0',
      })
    },

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

          this.error_msg = error_message

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

    //resend login code by another medium (ie. SMS)
    resendCode() { 
      this.call('auth.resendCode', {
        phone_number: this.phone_num,
        phone_code_hash: this.phone_hash
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
        this.stepperGo(5)
        this.$store.dispatch('auth/updateTGUser', result.user)
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
            this.stepperGo(5)
          })
          .catch(error => {
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
        title: this.group_name,
        about: this.group_desc
      })
      .then(result => {
        const group = result.chats[0]
        this.$store.dispatch('auth/updateTGGroup', group)

        this.botAdd()
        this.updateDB(group)

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

      const botUser = {
        _: 'inputUser',
        user_id: bot.id,
        access_hash: bot.access_hash
      }
      const channel = {
        _: 'inputChannel',
        channel_id: this.tgGroup.id,
        access_hash: this.tgGroup.access_hash
      }
      const adminRights = {
        _: 'chatAdminRights',
        change_info: true,
        post_messages: true,
        edit_messages: true,
        delete_messages: true,
        ban_users: true,
        invite_users: true,
        pin_messages: true,
        add_admins: true        
      }
      var photo = {}
      this.call('photos.getUserPhotos', {
        user_id: {
          _: 'inputUser',
          user_id: bot.id,
          access_hash: bot.access_hash
        }
      })
      .then(photos => {
        const p = photos.photos[0]
        photo = {
          _: 'inputChatPhoto',
          id: {
            _: 'inputPhoto',
            id: p.id,
            access_hash: p.access_hash,
            file_reference: p.file_reference
          }
        }
      })



      // Add the bot to the channel
      this.call('channels.inviteToChannel', {
        channel: channel,
        users: [botUser]
      })
      .then(update => {
        // Make the bot an admin
        this.call('channels.editAdmin', {
          channel: channel,
          user_id: botUser,
          admin_rights: adminRights,
          rank: ''
        })

        // Change group picture
        this.call('channels.editPhoto', {
          channel: channel,
          photo: photo
        })
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


    async updateDB (group) {
      await axios({
        method: 'post',      
        url: '/api/teams/' + this.team.id + '/notificationsettings',
        data: {
          channel_id: group.id,
          access_hash: group.access_hash
        }
      })
      
      //await this.setGroupLink(group.id)

    },

    async disableNotifications() {
      if (await this.$root.$confirm(this.$t('notifications.confirm_disable_notifications'), null, 'error')) {

        await axios({
          method: 'post',      
          url: '/api/teams/' + this.team.id + '/notificationsettings',
          data: {
            reset: true
          }
        })
        .then(response => {
          this.showSnackbar(this.$t('notifications.success_disable_notifications'), 'success')
        })
      }
    },


    async setGroupLink(channel_id) {
      const chat_id = '-100' + channel_id
      const url = this.bot_api_base + 'exportChatInviteLink?chat_id=' + chat_id
      var link = null

      await axios({
        method: 'get',      
        url: url,
      })
      .then(response => {
        link = response.data.result

        axios({
          method: 'post',
          url: '/api/teams/' + this.team.id + '/grouplink',
          data: {
            group_link: link
          }
        })

      })

      return link
    },

    async getGroupLink() {
      var channel_id = null
      var link = null

      await axios({
        method: 'get',      
        url: '/api/teams/' + this.team.id + '/grouplink',
      })
      .then(response => {
        channel_id = response.data.channel_id
        link = response.data.link
      })

      const data = {
        channel_id: channel_id,
        link: link
      }

      return data
    },

    async signInBot() {
      this.call('auth.importBotAuthorization', {
        api_id: this.api_id,
        api_hash: this.api_hash,
        bot_auth_token: this.bot_token
      })
    },

    async getChannelInfo () {
      
      await axios({
        method: 'get',      
        url: '/api/teams/' + this.team.id + '/notificationsettings',
      })
      .then(response => {
        const settings = response.data.settings
        
        // Create an invitation link
        const peerChannel = {
          _: 'inputPeerChannel',
          channel_id: settings.telegram_channel_id,
          access_hash: settings.telegram_access_hash
        };
        console.log(peerChannel)

        this.call('messages.exportChatInvite', {
          peer: peerChannel
        })
        .then(result => {
          console.log(result)
        })
            
        
      })

    },



    async sendMessage(channel_id, message_text) {
      const chat_id = '-100' + channel_id
      const url = this.bot_api_base + 'sendMessage?chat_id=' + chat_id + '&text=' + message_text
      var message = null

      await axios({
        method: 'get',      
        url: url,
      })
      .then(response => {
        message = response.data.result
      })

      return message
    },
  }
}

export default mtproto