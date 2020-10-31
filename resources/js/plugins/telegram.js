import { MTProto, getSRPParams } from '@mtproto/core'
import { mapGetters } from 'vuex'

const mtproto = {
  data () {
    return {
      mtproto: null,
      api_id: '1963687',
      api_hash: '756735263dc2835ebfd1e02dfdf5dcbd',
      bot_token: '1363093542:AAFVY3NrG95hkINydWq5EumOuI4QwApNIXI',
      bot_id: '1363093542',
      bot_hash: '11592732920764892291',
      phone_hash: null,
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

    sendCode() {
      this.call('auth.logOut')
      this.call('auth.sendCode', {
        phone_number: this.phoneNum,
        settings: {
          _: 'codeSettings',
        },
      })
    },

    botJoinSelf () {
      this.call('auth.logOut')

      this.call('auth.importBotAuthorization', {
        api_id: this.api_id,
        api_hash: this.api_hash,
        bot_auth_token: this.bot_token
      })
      .then(result => {
      })
    },

    botAdd () {
      const botUser = {
        _: 'inputUser',
        user_id: this.bot_id,
        access_hash: this.bot_hash
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

    signIn() {
      this.call('auth.signIn', {
        phone_code: this.code,
        phone_number: this.phoneNum,
        phone_code_hash: this.phone_hash,
      })
      .then(result => {
        this.$store.dispatch('auth/updateTGUser', result.user)
      })
      .catch(error => {
        if (error.error_message === 'SESSION_PASSWORD_NEEDED') {
          this.getPassword().then(async result => {
            const { srp_id, current_algo, srp_B } = result;
            const { g, p, salt1, salt2, } = current_algo;
  
            const { A, M1 } = await getSRPParams({
              g,
              p,
              salt1,
              salt2,
              gB: srp_B,
              password,
            });
  
            return this.checkPassword({ srp_id, A, M1 });
          });
        }
  
        return Promise.reject(error);
      })
    },

    signOut () {
      this.call('auth.logOut')
      this.$store.dispatch('auth/updateTGUser', null)
    },

    getPassword() {
      this.call('account.getPassword');
    },
    
    async checkPassword(srp_id, A, M1) {
      this.call('auth.checkPassword', {
        password: {
          _: 'inputCheckPasswordSRP',
          srp_id,
          A,
          M1,
        },
      });
    },

    getUserInfo () {
      console.log(this.tgUser)
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
    }
  }
}

export default mtproto