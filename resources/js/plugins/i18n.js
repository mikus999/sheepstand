import Vue from 'vue'
import store from '~/store'
import VueI18n from 'vue-i18n'
import dayjs from 'dayjs';

Vue.use(VueI18n)



 export function loadLocaleMessages() {
  const context = require.context("~/lang", true, /[A-Za-z0-9-_,\s]+\.json$/i);
  const contextDjs = require.context('dayjs/locale/', true, /\.js$/)
  const messages = {};
  const formats = {};

  // Loop through each translated app language (has file in '~/lang/' folder)
  context.keys().forEach(key => {
    const matched = key.match(/([A-Za-z0-9-_]+)\./i);

    if (matched && matched.length > 1) {
      // Load vue-i18n locale
      const locale = matched[1];
      messages[locale] = context(key);


      // Load day.js formats for this locale
      contextDjs.keys().forEach(keyDjs => {
        const parsed = keyDjs.match(/([A-Za-z0-9-_]+)\./i);
        const localeDjs = parsed[1]
        if (localeDjs === locale) {
          //window['dayjs_locale_'+localeDjs] = contextDjs(keyDjs)
        }
      })
    }
  });

  return { context, messages };
}

const { context, messages } = loadLocaleMessages();


const i18n = new VueI18n({
  locale: store.getters['lang/locale'],
  fallbackLocale: 'en',
  silentFallbackWarn: true,
  messages: messages
})



export default i18n
