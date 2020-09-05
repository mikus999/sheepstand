<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        {{ $t('translation.management_system') }}
      </h1>
    </v-row>

    <v-row>
      <v-col cols=6>
        <h4>{{ $t('general.language')}}: EN</h4>
      </v-col>

      <v-col cols=6>

      </v-col>      

    </v-row>
  </v-container>
</template>

<script>

export default {
  middleware: 'auth',
  layout: 'vuetify',

  components: {
  },

  data: () => ({
  }),


}
</script>
