<template>
  <v-container fluid>
    <Login />
  </v-container>
</template>

<script>
import Login from '~/components/Login'

export default {
  layout: 'sidebar',
  middleware: 'guest',
  components: {
    Login
  }
}
</script>
