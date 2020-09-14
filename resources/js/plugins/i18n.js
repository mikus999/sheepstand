import Vue from 'vue'
import store from '~/store'
import VueI18n from 'vue-i18n'

Vue.use(VueI18n)



 export function loadLocaleMessages() {
  const context = require.context("~/lang", true, /[A-Za-z0-9-_,\s]+\.json$/i);
  const messages = {};
  context.keys().forEach(key => {
    const matched = key.match(/([A-Za-z0-9-_]+)\./i);
    if (matched && matched.length > 1) {
      const locale = matched[1];
      messages[locale] = context(key);
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
