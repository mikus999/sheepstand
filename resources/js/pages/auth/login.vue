<template>
  <v-container fluid>
    <Login max-width="500" />
  </v-container>
</template>

<script>
import Login from '~/components/Login'

export default {
  layout: 'default',
  middleware: 'guest',
  components: {
    Login
  }
}
</script>
