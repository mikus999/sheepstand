

// Components that are registered globaly.
[

].forEach(Component => {
  Vue.component(Component.name, Component)
})
