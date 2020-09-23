import store from '~/store'

export default async (to, from, next) => {

  // Fetch user data, teams (If logged in (has token), but has not fetched user data)
  if (!store.getters['auth/check'] && store.getters['auth/token']) {
    try {
      // Fetch the user
      await store.dispatch('auth/fetchUser')

    } catch (e) { }

  }

  next()
}
