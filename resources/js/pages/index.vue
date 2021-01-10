<template>
  <div>
    Welcome.vue
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  layout: 'sidebar',


  data: () => ({
  }),

  computed: mapGetters({
    authenticated: 'auth/check'
  })
}
</script>

<style scoped>
.top-right {
  position: absolute;
  right: 10px;
  top: 18px;
}

.title {
  font-size: 85px;
}
</style>
