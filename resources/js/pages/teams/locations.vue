<template>
  <v-container>
    <v-row>
      <h1 class="display-1">
        Locations
      </h1>
    </v-row>

    <v-row>
      <v-col md="12">
        <v-data-table :headers="headers" :items="locationData" sort-by="default, name" sort-desc>
          <template v-slot:top>
            <v-toolbar flat>
              <v-toolbar-title>Cart Locations</v-toolbar-title>
              <v-spacer></v-spacer>
              <v-btn color="secondary" class="mb-2" @click="showDialog(tempData, false)">Create New Location</v-btn>


              <!-- NEW/EDIT DIALOG -->
              <v-dialog v-model="dialog" max-width="500px">
                <v-card>
                  <v-card-title class="text-center">
                    <span class="headline">Cart Location</span>
                  </v-card-title>

                  <v-card-text>
                    <v-container>
                      <v-text-field v-model="tempData.name" prepend-icon="mdi-form-textbox" label="Location Name" />

                      <v-menu v-model="menu" top nudge-bottom="105" nudge-left="16" :open-on-click="true" :close-on-content-click="true">
                        <template v-slot:activator="{ on }">
                          <v-text-field v-model="tempData.color_code" label="Display Color (optional)" v-on="on" prepend-icon="mdi-palette" hide-details >
                              <template v-slot:prepend-inner>
                                <v-icon :color="tempData.color_code">mdi-square-rounded</v-icon>
                              </template>
                            </v-text-field>
                        </template>
                        <v-card>
                          <v-card-text class="pa-0">
                            <v-color-picker v-model="tempData.color_code" flat />
                          </v-card-text>
                        </v-card>
                      </v-menu>
                      
                      <v-file-input show-size label="Map/Route File (optional)" prepend-icon="mdi-map"></v-file-input>
                       
                      <!-- DEFAULT CHECKBOX -->
                  
                    </v-container>
                  </v-card-text>

                  <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="secondary" text @click="close">Cancel</v-btn>
                    <v-btn color="secondary" text @click="createOrUpdate">
                      {{ isEdit ? 'Save' : 'Create' }}
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>

            </v-toolbar>
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

        <v-snackbar v-model="snack" :timeout="3000" :color="snackColor">
          {{ snackText }}

          <template v-slot:action="{ attrs }">
            <v-btn v-bind="attrs" text @click="snack = false">Close</v-btn>
          </template>
        </v-snackbar>

      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import Form from 'vform'
import helper from '../../mixins/helper'
import moment from 'moment'

export default {
  middleware: 'auth',
  layout: 'vuetify',
  mixins: [helper],

  data () {
    return {
      dialog: false,
      isEdit: false,
      headers: [
        { text: 'Location Name', align: 'start', value: 'name' },
        { text: 'Display Color', value: 'color_code', sortable: false},
        { text: 'Map/Route File', value: 'map', sortable: false },
        { text: 'Actions', value: 'actions', sortable: false },
      ],
      defaultData: {
        id: null,
        team_id: null,
        name: '',
        color_code: '',
        map: '',
        default: false
      },
      tempData: [],
      locationData: [],
      menu: false,
      snack: false,
      snackText: '',
      snackColor: ''
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
    }
  },

  created () {
    this.getData()
  },

  methods: {
    
    async getData () {
      await axios.get('/api/teams/' + this.formatJSON(this.team).id + '/locations/')
        .then(response => {
          this.locationData = response.data
        })
    },

    deleteLoc (loc) {
      const index = this.locationData.indexOf(loc.id)
      if (confirm('Are you sure you want to delete this location? This will also delete all shifts using this location.')) {
        axios.delete('/api/teams/' + this.formatJSON(this.team).id + '/locations/' + loc.id)
          .then(response => {
            this.locationData.splice(index, 1)  
            this.snack = true
            this.snackColor = 'success'
            this.snackText = response.data.message
          })

      }
    },

    close () {
      this.dialog = false
    },


    showDialog (data, edit) {
      if (!edit) {
        this.tempData = this.lodash.cloneDeep(this.defaultData)
        this.tempData.team_id = this.formatJSON(this.team).id
      } else {
        this.tempData = this.lodash.cloneDeep(data)
      }

      this.isEdit = edit
      this.dialog = true
    },


    createOrUpdate () {
      
      if (!this.isEdit) {
        var aMethod = 'post'
        var aUrl = '/api/teams/' + this.formatJSON(this.team).id + '/locations/'
      } else {
        var aMethod = 'patch'
        var aUrl = '/api/teams/' + this.formatJSON(this.team).id + '/locations/' + this.tempData.id
      }

      axios({
        method: aMethod,      
        url: aUrl,
        data: this.tempData
      })
      .then(response => {
        this.getData()

        this.snack = true
        this.snackColor = 'success'
        this.snackText = "Location successfully updated"
      })

      this.close()
    },

  }
}

</script>