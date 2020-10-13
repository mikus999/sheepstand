<template>
  <div class="mb-6">
    <div v-if="$vuetify.breakpoint.xs" class="text-h6">
      {{ title }}
    </div>

    <div v-else class="text-h4">
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