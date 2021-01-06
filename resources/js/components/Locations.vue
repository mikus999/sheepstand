<template>
  <v-card width="100%" :flat="$vuetify.breakpoint.xs">
    <v-data-table :headers="headers" :items="locationData" sort-by="default, name" sort-desc>
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title v-if="showTitle || $vuetify.breakpoint.xs">
            <v-icon left>mdi-map-marker-multiple</v-icon>
            {{ $t('teams.cart_locations') }}
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn 
            color="secondary" 
            class="mb-2" 
            @click="showDialog(tempData, false)" 
          >
            <v-icon 
              :left="$vuetify.breakpoint.smAndUp"
              :small="$vuetify.breakpoint.smAndUp"
            >mdi-map-marker-plus</v-icon>
            <span v-if="$vuetify.breakpoint.smAndUp">
              {{ $t('teams.create_new_location') }}
            </span>
          </v-btn>


          <!-- NEW/EDIT DIALOG -->
          <v-dialog v-model="dialog" max-width="500px">
            <v-card>
              <v-card-title class="text-center">
                <span class="headline">{{ $t('teams.create_new_location') }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-text-field v-model="tempData.name" prepend-icon="mdi-form-textbox" :label="$t('teams.location_name')" 
                    :error-messages="nameErrors" @blur="$v.tempData.name.$touch()" />

                  <v-menu v-model="menu" top nudge-bottom="105" nudge-left="16" :open-on-click="true" :close-on-content-click="false">
                    <template v-slot:activator="{ on }">
                      <v-text-field v-model="tempData.color_code" :label="$t('teams.location_color_optional')" v-on="on" prepend-icon="mdi-palette" hide-details >
                          <template v-slot:prepend-inner>
                            <v-icon :color="tempData.color_code">mdi-square-rounded</v-icon>
                          </template>
                        </v-text-field>
                    </template>
                    <v-card>
                      <v-card-text class="pa-0">
                        <v-color-picker 
                          v-model="tempData.color_code" 
                          flat 
                          hide-canvas
                          hide-inputs
                          hide-mode-switch
                          mode="hexa"
                          :swatches="swatches"
                          show-swatches
                        />
                      </v-card-text>
                      <v-card-actions>
                        <v-spacer />
                        <v-btn text @click="menu = false">{{ $t('general.ok') }}</v-btn>
                      </v-card-actions>
                    </v-card>
                  </v-menu>
                  
                  <!--<v-file-input v-model="tempData.map" show-size :label="$t('teams.location_map_optional')" prepend-icon="mdi-map"></v-file-input>-->
                    
                  <!-- DEFAULT CHECKBOX -->
              
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn text @click="close">{{ $t('general.cancel') }}</v-btn>
                <v-btn color="primary" @click="createOrUpdate">
                  {{ isEdit ? $t('general.save') : $t('general.create') }}
                </v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

        </v-toolbar>
      </template>
      
      <template v-slot:item.color_code="{ item }">
        <v-chip :color="item.color_code" label small>{{ item.color_code }}</v-chip>
      </template>
    
      <template v-slot:item.map="{ item }">
        <v-chip label small @click="showLocationOverlay(item)">Edit Map</v-chip>
      </template>

      <template v-slot:item.default="{ item }">
        <v-icon v-if="item.default" color="green">mdi-check-circle</v-icon>
        <v-icon v-else @click.prevent="updateDefault(item.id)">mdi-circle-outline</v-icon>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-btn icon @click="showDialog(item, true)">
          <v-icon>mdi-pencil</v-icon>
        </v-btn>

        <v-btn icon @click="deleteLoc(item)">
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <v-overlay :value="locationOverlay" @click.native="locationOverlay = false" :dark="theme=='dark'">
      <Leaflet :location="location" :width="mapWidth" height="500px"  
          v-on:close="locationOverlay = false" v-on:click.native.stop/>
    </v-overlay>
  </v-card>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import { required } from 'vuelidate/lib/validators'
import Leaflet from '~/components/Leaflet.vue'
import { cloneDeep } from 'lodash'

export default {
  name: "Locations",
  mixins: [helper],
  components: {
    Leaflet
  },

  validations: {
    tempData: {
       name: { required },
    },
  },

  props: {
    showTitle: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      dialog: false,
      isEdit: false,
      locationOverlay: false,
      headers: [
        { text: this.$t('teams.location_name'), align: 'start', value: 'name' },
        { text: this.$t('teams.location_color'), value: 'color_code', align: 'center', sortable: false},
        { text: this.$t('teams.location_map'), value: 'map', sortable: false },
        { text: this.$t('general.default'), value: 'default', align: 'center', sortable: false },
        { text: this.$t('general.actions'), value: 'actions', sortable: false },
      ],
      defaultData: {
        id: null,
        team_id: null,
        name: '',
        color_code: '#7E7E7E',
        map: null,
        default: false
      },
      swatches: [
        ['#FF5A5A', '#FF4081', '#BA68C8'], // reds
        ['#00D3E6', '#4793CA', '#AEAEAE'], // blues
        ['#AFB42B', '#00E676', '#26A69A'],
        ['#FF9800', '#EF6C00', '#A1887F'] // oranges
      ],
      tempData: {
        name: null
      },
      locationData: [],
      location: null,
      menu: false,
    }
  },

  computed: {

    swatchStyle() {
      const { color, menu } = this
      return {
        backgroundColor: this.tempData.color_code,
        cursor: 'pointer',
        height: '20px',
        width: '20px',
        borderRadius: menu ? '50%' : '4px',
        transition: 'border-radius 200ms ease-in-out'
      }
    },

    nameErrors () {
      const errors = []
      if (!this.$v.tempData.name.$dirty) return errors
      !this.$v.tempData.name.required && errors.push(this.$t('teams.location_name_required'))
      return errors
    },

    mapWidth() {
      var newWidth = this.$vuetify.breakpoint.width < 500 ? (this.$vuetify.breakpoint.width - 50) + 'px' : '500px'
      return newWidth
    }
  },

  created () {
    this.getData()
  },

  methods: {
    
    async getData () {
      await axios.get('/api/teams/' + this.team.id + '/locations/')
        .then(response => {
          this.locationData = response.data
        })
    },

    async deleteLoc (loc) {
      const index = this.locationData.indexOf(loc.id)
      if (await this.$root.$confirm(this.$t('teams.confirm_location_delete'), null, 'error')) {
        await axios.delete('/api/teams/' + this.team.id + '/locations/' + loc.id)
          .then(response => {
            this.locationData.splice(index, 1)  
            this.showSnackbar(this.$t('teams.success_location_delete'), 'success')
          })

      }
    },

    close () {
      this.dialog = false
    },


    showDialog (data, edit) {
      if (!edit) {
        this.tempData = cloneDeep(this.defaultData)
        this.tempData.team_id = this.team.id
      } else {
        this.tempData = cloneDeep(data)
      }

      this.isEdit = edit
      this.dialog = true
    },


    async createOrUpdate () {
      this.$v.$touch()

      if (!this.$v.$invalid) {
        if (!this.isEdit) {
          var aMethod = 'post'
          var aUrl = '/api/teams/' + this.team.id + '/locations/'
        } else {
          var aMethod = 'patch'
          var aUrl = '/api/teams/' + this.team.id + '/locations/' + this.tempData.id
        }

        await axios({
          method: aMethod,      
          url: aUrl,
          data: this.tempData
        })
        .then(response => {
          this.getData()
          this.showSnackbar(this.$t('teams.success_location_update'), 'success')
        })

        this.close()

      }
    },

    async updateDefault (locid) {
      await axios({
        method: 'post',      
        url: '/api/teams/' + this.team.id + '/locations/' + locid + '/makedefault',
        data: this.tempData
      })
      .then(response => {
        this.getData()
        this.showSnackbar(this.$t('teams.success_location_default'), 'success')
      })
    },

    showLocationOverlay(location) {
      this.location = location
      this.locationOverlay = true
    },

  }
}

</script>