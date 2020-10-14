<template>
  <div class="mb-6">
    <div v-if="$vuetify.breakpoint.xs" class="header-xs">
      {{ title }}
    </div>

    <div v-else class="header-smAndUp">
      {{ title }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'PageTitle',
  props: ['title']
}
</script>

<style lang="scss">
  .header-xs {
    font-size: 1.25rem;
    font-weight: 500;
    font-family: 'Raleway', sans-serif !important;
  }

  .header-smAndUp {
    font-size: 2.125rem;
    font-weight: 400;
    font-family: 'Raleway', sans-serif !important;
  }
</style>
