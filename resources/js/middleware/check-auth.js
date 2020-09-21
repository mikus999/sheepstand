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


  // If logged in and has user data
  if (store.getters['auth/check']) {
    // If role data has not been fetched
    if (store.getters['auth/roles'] === null) {
      //const team = await store.getters['teams/getTeam']
      store.dispatch('auth/fetchRoles')
    }
  }

  next()
}
