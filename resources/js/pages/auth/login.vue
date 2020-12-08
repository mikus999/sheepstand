<template>
  <v-container fluid>
    <Login />
  </v-container>
</template>

<script>
import Login from '~/components/Login'

export default {
  layout: 'vuetify',
  middleware: 'guest',
  components: {
    Login
  }
}
</script>
