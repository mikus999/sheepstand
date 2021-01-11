<template>
  <v-container fluid class="pa-0 ma-0 grey lighten-2" fill-height>
    <v-row class="full-height" justify="center" align="center">

      <v-col cols=12 md=7 class="pa-8 pa-lg-16 bg-crowd" v-if="$vuetify.breakpoint.mdAndUp">
        <div class="text-overlay d-flex flex-column pa-8 pa-lg-16">
          <span class="text-h3 nunito white--text text-intro">{{ $t('public.tagline1') }}</span>
          <span class="text-body-1 nunito white--text mt-6">{{ $t('public.site_description1') }}</span>
        </div>
      </v-col>

      <v-col cols=12 class="d-flex tagline-mobile" v-else>
        <div class="nunito align-center">
          {{ $t('public.tagline2') }}
        </div>
      </v-col>

      <v-col cols=12 md=5 class="align-center pa-8">

        <Login v-if="!user" />
        <MyShifts v-else show-mobile />
      </v-col>

    </v-row>

    <v-row class="row-title">
      <v-col cols=12 class="d-flex flex-column align-center">
        <span class="text-h3 text-sm-h2 nunito">{{ $t('public.flexible') }}</span>
      </v-col>
    </v-row>

    <v-row class="row-description px-4 px-sm-16">
      <v-col cols=12 class="d-flex flex-column align-center">
        <span class="text-h6 text-sm-h5 nunito">
          {{ $t('public.flexible_desc1')}} 
          {{ $t('public.flexible_desc2')}}
        </span>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { mapGetters } from 'vuex'
import Login from '~/components/Login.vue'
import MyShifts from '~/components/MyShifts.vue'

export default {
  layout: 'default',
  components: {
    Login,
    MyShifts
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
    }),

    isMobile () {
      return this.$vuetify.breakpoint.mobile
    },
  },

}

</script>


<style scoped>

  .full-height {
    height: 95vh !important;
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
    opacity: .8;
    border-radius: 10px;
    justify-content: center;
    align-items: left;
  }

  .nunito {
    font-family: 'Nunito' !important;
  }

  .text-intro {
    line-height: 1.2em;
  }

  .tagline-mobile {
    font-size: 1.2em;
    font-weight: bold;
    text-transform: uppercase;
    justify-content: center;
    align-items: left;
    margin-top: 20px;
  }

  .row-title {
    height: 200px;
    width: 100%;
    margin-top: 60px;
    align-items: center;
    justify-content: center;
  }

  .row-description {
    min-height: 300px;
    width: 100%;
    justify-content: center;
  }
</style>
