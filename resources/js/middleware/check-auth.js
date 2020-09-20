import store from '~/store'

export default async (to, from, next) => {

  // Fetch user data, teams (If logged in (has token), but has not fetched user data)
  if (!store.getters['auth/check'] && store.getters['auth/token']) {
    try {
      // Fetch the user
      await store.dispatch('auth/fetchUser')

      // Fetch the teams.
      await store.dispatch('teams/fetchTeams');

    } catch (e) { }
  }


  // Fetch roles (if logged in, but has not fetched roles)
  if (store.getters['auth/roles'] === null && store.getters['auth/check']) {
    //const team = await store.getters['teams/getTeam']
    store.dispatch('auth/fetchRoles')
  }

  next()
}
