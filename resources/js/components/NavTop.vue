<template>
  <v-app-bar dark absolute dense class="light-blue darken-4 white--text text-decoration-none" app>
    <v-app-bar-nav-icon @click.stop="$emit('toggle-drawer')"></v-app-bar-nav-icon>

    <v-toolbar-title>
      <span class="head-thick">CART</span>
      <span class="head-thin ml-n1">PLAN</span>
    </v-toolbar-title>
  </v-app-bar>
</template>


<script>
export default {
  
}
</script>