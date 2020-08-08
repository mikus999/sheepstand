import store from '~/store'

export default function teams ({ next, store }) {
  if (!store.getters['teams/hasTeam']) {
    next({ name: 'jointeam' })
  } else {
    next()
  }
}
