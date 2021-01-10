<template>
  <v-container fluid class="pa-0 ma-0" fill-height>
    <v-row class="full-height" justify="center" align="center">

      <v-col cols=12 sm=7 class="pa-16 bg-crowd">
        <div class="text-overlay">
          Just a test
        </div>
      </v-col>

      <v-col cols=12 sm=5 class="align-center">
        <Login />
      </v-col>

    </v-row>

    <v-row class="full-height">
      <v-col cols="12">
        TEST
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Login from '~/components/Login.vue'

export default {
  layout: 'default',
  components: {
    Login
  },

}

</script>


<style scoped>
  .full-height {
    height: 102% !important;
  }

  .bg-crowd {
    background-image: url("/images/coverImg2.jpg");
    height: 100%;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
  }

  .text-overlay {
    height: 100%;
    width: 100%;
    background-color: #000000;
    opacity: .7;
    border-radius: 10px;
  }
</style>
