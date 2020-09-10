import store from '~/store'
import { loadMessages } from '~/plugins/i18n'

export default async (to, from, next) => {
  //await loadLocaleMessages()

  next()
}
