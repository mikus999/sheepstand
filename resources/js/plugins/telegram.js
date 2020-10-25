import { MTProto, getSRPParams } from '@mtproto/core'

const mtproto = {
  data () {
    return {
      mtproto: null,
      api_id: '1963687',
      api_hash: '756735263dc2835ebfd1e02dfdf5dcbd',
      phone_hash: null,
    }
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
  },

  methods: {
    call(method, params, options = {}) {
      return this.mtproto.call(method, params, options)
        .then(result => {
          this.phone_hash = result.phone_code_hash
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
      this.call('auth.sendCode', {
        phone_number: this.phoneNum,
        settings: {
          _: 'codeSettings',
        },
      })
    },

    signIn() {
      this.call('auth.signIn', {
        phone_code: this.code,
        phone_number: this.phoneNum,
        phone_code_hash: this.phone_hash,
      })
      .then(result => {
        console.log(result)
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
      this.call('help.getUserInfo', {
        user_id: {
          _: 'inputUserSelf',
        },
      })
      .then(result => {
        console.log(result)
      })
      
    },
  }
}

export default mtproto