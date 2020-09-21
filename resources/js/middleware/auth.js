import store from '~/store'

export default (to, from, next) => {
  if (!store.getters['auth/check']) {
    next({ name: 'login' })
  } else {
    // IF LOGGED IN
    

    /**
     * 
     * Check permissions (roles, permissions)
     * Check if the route is protected
     * Then, wait for store to initialize
     * Then, call 'checkPermissions' function
     * 
     */
    if (to.meta.roles && (to.meta.roles.length > 0)) {
      // First, wait for store to initialize
      if (store.getters['auth/roles'] === null) {
        store.watch(() => store.getters['auth/roles'], r => {
          console.log('check 1')
          checkPermissions(to, from, next)
        })
      } else {
        console.log('check 2')
        checkPermissions(to, from, next)
      }

    } else {
      // If no roles or permissions are specified for the route, continue anyway
      console.log('check 3')
      next() 
    }




    // Check team membership
    var hasTeam = store.getters['teams/hasTeam']
    if (to.name !== 'teams.join' && !hasTeam) {
      next({ name: 'teams.join' })
    } else {
      next()
    }

  }
}





/**
 * Check if user can access the protected route
 * 
 * @param {Route} to
 * @param {Route} from
 * @param {Function} next
 */
function checkPermissions (to, from, next) {
  const routeRoles = to.meta.roles

  var isAllowed = false

  const userRoles = store.getters['auth/roles']
  Object.keys(userRoles).forEach(function(key) {
    if (routeRoles.indexOf(userRoles[key]) >= 0) {
      isAllowed = true
    }
  })

  if (isAllowed) {
    next()
  } else {
    next({ name: 'accessdenied' })
  }

  
}
