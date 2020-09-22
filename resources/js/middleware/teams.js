import store from '~/store'

export default async (to, from, next) => {
  if (store.getters['auth/check']) {
    
    // Check team membership
    var hasTeam = store.getters['teams/hasTeam']
    if (to.name !== 'teams.join' && !hasTeam) {
      next({ name: 'teams.join' })
    } else {
      next()
    }
  } else {
    next()
  }
}
