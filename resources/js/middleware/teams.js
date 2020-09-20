import store from '~/store'

export default async (to, from, next) => {
  var hasTeam = await store.getters['teams/hasTeam']
  if (to.name !== 'teams.join' && !hasTeam) {
    next({ name: 'teams.join' })
  } else {
    next()
  }
}
