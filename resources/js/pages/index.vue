<template>
  <v-container fluid class="pa-0 ma-0" fill-height>
    <v-row :class="($vuetify.breakpoint.mdAndUp ? 'full-height' : '')" justify="center" align="center">

      <v-col cols=12 md=7 class="pa-8 pa-lg-16 bg-crowd" v-if="$vuetify.breakpoint.mdAndUp">
        <div class="text-overlay d-flex flex-column pa-8 pa-lg-16">
          <span class="text-h3 nunito white--text text-intro blockquote">{{ $t('public.tagline1') }}</span>
          <span class="text-body-1 nunito white--text blockquote text-justify">{{ $t('public.site_description1') }}</span>
        </div>
      </v-col>

      <v-col cols=12 class="d-flex tagline-mobile text-h6 text-sm-h4" v-else>
        <div class="nunito align-center">
          {{ $t('public.tagline2') }}
        </div>
      </v-col>

      <v-col cols=12 md=5 :class="'pa-8' + ($vuetify.breakpoint.mdAndUp ? ' cell-full-height grey lighten-1' : '')">
        <div :class="'d-flex flex-column' + ($vuetify.breakpoint.mdAndUp ? ' components-right' : '')">
          <Login v-if="!user" />
          <MyShifts v-else show-mobile />
        </div>
      </v-col>

    </v-row>

    <v-row>
      <v-col cols=12 md=6>
        <div class="row-title">
          <div class="d-flex flex-column align-center">
            <span class="text-h3 text-sm-h2 nunito">{{ $t('public.flexible') }}</span>
          </div>
        </div>

        <div class="row-description">
          <div class="d-flex flex-column px-4 px-sm-16 align-center">
            <span class="text-h6 text-sm-h5 nunito">
              {{ $t('public.flexible_desc1')}} 
              {{ $t('public.flexible_desc2')}}
            </span>
          </div>
          <div class="d-flex flex-column px-4 px-sm-16 py-8 align-center">
            <div class="text-h6 text-sm-h5 nunito">
              <p><v-icon class="list-icon">{{ icons.mdiCalendarMultiselect }}</v-icon>{{ $t('public.flexible_feature1') }}</p>
              <p><v-icon class="list-icon">{{ icons.mdiAccountGroup }}</v-icon>{{ $t('public.flexible_feature2') }}</p>
              <p><v-icon class="list-icon">{{ icons.mdiCalendarStar }}</v-icon>{{ $t('public.flexible_feature3') }}</p>
              <p><v-icon class="list-icon">{{ icons.mdiListStatus }}</v-icon>{{ $t('public.flexible_feature4') }}</p>
            </div>
          </div>
        </div>
      </v-col>
    
      <v-col cols=12 md=6>
        <div class="row-title">
          <div class="d-flex flex-column align-center">
            <span class="text-h3 text-sm-h2 nunito">{{ $t('public.user_friendly') }}</span>
          </div>
        </div>

        <div class="row-description">
          <div class="d-flex flex-column px-4 px-sm-16 align-center">
            <span class="text-h6 text-sm-h5 nunito">
              {{ $t('public.uf_desc1')}} 
              {{ $t('public.uf_desc2')}}
            </span>
          </div>
          
          <div class="d-flex flex-column px-4 px-sm-16 py-8 align-center">
            <div class="text-h6 text-sm-h5 nunito">
              <p><v-icon class="list-icon">{{ icons.mdiTabletCellphone }}</v-icon>{{ $t('public.uf_feature1') }}</p>
              <p><v-icon class="list-icon">{{ icons.mdiTranslate }}</v-icon>{{ $t('public.uf_feature2') }}</p>
              <p><v-icon class="list-icon">{{ icons.mdiMapMarkerMultiple }}</v-icon>{{ $t('public.uf_feature3') }}</p>
              <p><v-icon class="list-icon">{{ icons.mdiPalmTree }}</v-icon>{{ $t('public.uf_feature4') }}</p>
            </div>
          </div>
        </div>
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

  .cell-full-height {
    height: 100%;
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

  .components-right {
    height: 100%;
    justify-content: center;
  }

  .nunito {
    font-family: 'Nunito' !important;
  }

  .text-intro {
    line-height: 1.2em;
  }

  .tagline-mobile {
    font-weight: bold;
    text-transform: uppercase;
    justify-content: center;
    align-items: left;
    margin-top: 40px;
  }

  .row-title {
    width: 100%;
    margin: 60px 0px;
    align-items: center;
    justify-content: center;
  }

  .row-description {
    min-height: 300px;
    width: 100%;
    text-align: justify;
    justify-content: center;
  }

  .list-icon {
    margin-right: 20px;
  }
</style>
