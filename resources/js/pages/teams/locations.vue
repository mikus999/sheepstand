<template>
  <v-container fluid>
    <v-row>
      <PageTitle :title="$tc('teams.cart_location', 1)"></PageTitle>
    </v-row>

    <v-row>
      <v-col md="12">
        <v-data-table :headers="headers" :items="locationData" sort-by="default, name" sort-desc>
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title v-show="$vuetify.breakpoint.smAndUp">{{ $tc('teams.cart_location', 1) }}</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-btn color="secondary" class="mb-2" @click="showDialog(tempData, false)" :block="$vuetify.breakpoint.xs">{{ $t('teams.create_new_location') }}</v-btn>


              <!-- NEW/EDIT DIALOG -->
              <v-dialog v-model="dialog" max-width="500px">
                <v-card>
                  <v-card-title class="text-center">
                    <span class="headline">{{ $tc('teams.cart_location', 0) }}</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-text-field v-model="tempData.name" prepend-icon="mdi-form-textbox" :label="$t('teams.location_name')" 
                        :error-messages="nameErrors" @blur="$v.tempData.name.$touch()" />

                      <v-menu v-model="menu" top nudge-bottom="105" nudge-left="16" :open-on-click="true" :close-on-content-click="true">
                        <template v-slot:activator="{ on }">
                          <v-text-field v-model="tempData.color_code" :label="$t('teams.location_color_optional')" v-on="on" prepend-icon="mdi-palette" hide-details >
                              <template v-slot:prepend-inner>
                                <v-icon :color="tempData.color_code">mdi-square-rounded</v-icon>
                              </template>
                            </v-text-field>
                        </template>
                        <v-card>
                          <v-card-text class="pa-0">
                            <v-color-picker v-model="tempData.color_code" mode="hexa" flat />
                          </v-card-text>
                        </v-card>
                      </v-menu>
                      
                      <!--<v-file-input v-model="tempData.map" show-size :label="$t('teams.location_map_optional')" prepend-icon="mdi-map"></v-file-input>-->
                       
                      <!-- DEFAULT CHECKBOX -->
                  
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="secondary" text @click="close">{{ $t('general.cancel') }}</v-btn>
                    <v-btn color="secondary" text @click="createOrUpdate">
                      {{ isEdit ? $t('general.save') : $t('general.create') }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>

            </v-toolbar>
          </template>
          
          <template v-slot:item.color_code="{ item }">
            <v-chip :color="item.color_code" small>{{ item.color_code }}</v-chip>
          </template>

          <template v-slot:item.default="{ item }">
            <v-icon v-if="item.default" color="green">mdi-check-circle</v-icon>
            <v-icon v-else @click.prevent="updateDefault(item.id)">mdi-circle-outline</v-icon>
          </template>

          <template v-slot:item.actions="{ item }">
            <v-icon small @click="showDialog(item, true)" class="mr-2">
              mdi-pencil
            </v-icon>
            <v-icon small @click="deleteLoc(item)" class="mr-2">
              mdi-delete
            </v-icon>
          </template>
        </v-data-table>

      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import Form from 'vform'
import helper from '~/mixins/helper'
import { required } from 'vuelidate/lib/validators'

export default {
  middleware: ['auth', 'teams'],
  layout: 'vuetify',
  mixins: [helper],

  validations: {
    tempData: {
       name: { required },
    },
  },

  data () {
    return {
      dialog: false,
      isEdit: false,
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
        color_code: '#000000',
        map: null,
        default: false
      },
      tempData: {
        name: null
      },
      locationData: [],
      menu: false,
    }
  },

  computed: {
    ...mapGetters({
      user: 'auth/user',
      team: 'teams/getTeam',
    }),

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

    deleteLoc (loc) {
      const index = this.locationData.indexOf(loc.id)
      if (confirm('Are you sure you want to delete this location? This will also delete all shifts using this location.')) {
        axios.delete('/api/teams/' + this.team.id + '/locations/' + loc.id)
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
        this.tempData = this.lodash.cloneDeep(this.defaultData)
        this.tempData.team_id = this.team.id
      } else {
        this.tempData = this.lodash.cloneDeep(data)
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
    }

  }
}

</script>