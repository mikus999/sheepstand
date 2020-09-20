import store from '~/store'

export default async (to, from, next) => {
  if (!store.getters['auth/check'] && store.getters['auth/token']) {
    try {
      await store.dispatch('auth/fetchUser')
    } catch (e) { }
  }

  if (store.getters['auth/roles'] === null && store.getters['auth/check']) {
    var teamid = store.getters['teams/getTeam']
    if (teamid === null) {
      store.watch(() => store.getters['team/getTeam'], r => {
        store.dispatch('auth/fetchRoles', this.teamid)
      })
    } else {
      store.dispatch('auth/fetchRoles', teamid)
    }
  }

  next()
}
