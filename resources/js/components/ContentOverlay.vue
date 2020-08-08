<template>
  <div
    class="content-overlay"
    @click.prevent="closeMobileMenu"
  />
</template>

<script>

export default {

  methods: {

    closeMobileMenu () {
      window.bus.$emit('menu/toggle')
    }

  }

}

</script>
