<template>

  <gmap-map 
    ref="map" 
    :map-type-id="mapType"
    :center="mapCenter" 
    :zoom="13" 
    style="width: 100%; height: 800px" 
    :options="{
      zoomControl: true,
      mapTypeControl: false,
      scaleControl: false,
      streetViewControl: false,
      rotateControl: false,
      fullscreenControl: false,
      disableDefaultUi: false,
      draggable: readonly,
      draggableCursor: 'default'
    }"
    >
    
      <template #visible>
        <v-toolbar dense dark>
          <v-btn-toggle mandatory>
            <v-btn @click="updateMapType('roadmap')">
              <v-icon>mdi-map</v-icon>
            </v-btn>
            <v-btn @click="updateMapType('satellite')">
              <v-icon>mdi-satellite</v-icon>
            </v-btn>
          </v-btn-toggle>

          <v-spacer></v-spacer>

          <v-item-group class="v-btn-toggle" right>
            <v-btn @click="changeDrawingMode('marker')">
              <v-icon>mdi-map-marker</v-icon>
            </v-btn>
            <v-btn @click="changeDrawingMode('rectangle')">
              <v-icon>mdi-vector-rectangle</v-icon>
            </v-btn>
            <v-btn @click="changeDrawingMode('polygon')">
              <v-icon>mdi-vector-polygon</v-icon>
            </v-btn>
            <v-btn @click="">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
            <v-btn @click="saveShapes()">
              <v-icon>mdi-content-save</v-icon>
            </v-btn>
            <v-btn @click="loadGeoJSON()">
              <v-icon>mdi-upload</v-icon>
            </v-btn>
          </v-item-group>
        </v-toolbar>
      </template>

  </gmap-map>

</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'

export default {
  name: 'LocationMap',
  mixins: [helper],
  props: {
    location: {
      type: Object,
    },
    readonly: {
      type: Boolean,
      default: false
    },
    width: {
      type: [String, Number],
      default: '100%'
    },
    width: {
      type: [String, Number],
      default: '500px'
    },
    fill: {
      type: String,
      default: '#777777'
    }
  },

  data () {
    return {
      edited: null,
      mapCenter: {
        lat: 1,
        lng: 1
      },
      shapes: [],
      shapeOptions: {
        fillColor: this.fill,
        fillOpacity: 0.4,
        strokeWeight: 2,
        strokeColor: '#999999',
        draggable: true,
        editable: true,
        clickable: true
      },
      markerOptions: {
        draggable: true,
        clickable: true
      },
      locatorOptions: {
        enableHighAccuracy: true,
      },
      mapDraggable: this.readonly,
      mapCursor: this.readonly ? null : 'default',
      mapType: 'roadmap',
      drawingMode: 0,
      dataLayer: null,
      geoJson: null,
      drawingManager: null
    }
  },

  computed: {

  },

  created () {
    navigator.geolocation.getCurrentPosition(position => {
       this.mapCenter.lat = position.coords.latitude;
       this.mapCenter.lng = position.coords.longitude;
    }, null, this.locatorOptions)

  },

  mounted () {
    this.loadMapDrawingManager()
  },

  methods: {
    updateMapType (type) {
      this.mapType = type
    },

    changeDrawingMode (mode) {
      this.drawingManager.setDrawingMode(mode)
    },

    loadGeoJSON() {
      var jsonData = JSON.parse(this.location.map)
      var dataLayer = this.$refs.map.$mapObject.data
      
      dataLayer.setStyle({
        editable: true,
        clickable: true,
        draggable: true
      })
      
      var features = dataLayer.addGeoJson(jsonData)
      dataLayer.addListener('rightclick', (event) => {
        dataLayer.remove(event.feature)
      });
    },

    saveShapes () {
      var dataLayer = this.$refs.map.$mapObject.data

      dataLayer.toGeoJson((obj) => {
        console.log(obj)
        this.saveToDB(obj)
      });
    },

    /*
    shapesToGeoJSON() {
      const drawingManager = this.$refs.dm
      var dataLayer = new google.maps.Data()
      var geoJson = null

      this.shapes.forEach ((shape) => {

        switch (shape.type) {
          case 'marker':
            dataLayer.add(new google.maps.Data.Feature({
              geometry: new google.maps.Data.Point(shape.overlay.getPosition())
            }));
            break

          case 'rectangle':
            var b = shape.overlay.getBounds(),
              p = [b.getSouthWest(), {
                  lat: b.getSouthWest().lat(),
                  lng: b.getNorthEast().lng()
                },
                b.getNorthEast(), {
                  lng: b.getSouthWest().lng(),
                  lat: b.getNorthEast().lat()
                }
              ]
            dataLayer.add(new google.maps.Data.Feature({
              geometry: new google.maps.Data.Polygon([p])
            }));
            break

          case 'polygon':
            dataLayer.add(new google.maps.Data.Feature({
              geometry: new google.maps.Data.Polygon([shape.overlay.getPath().getArray()])
            }));
            break
          
          case 'polyline':
            dataLayer.add(new google.maps.Data.Feature({
              geometry: new google.maps.Data.LineString(shape.overlay.getPath().getArray())
            }));
            break

          case 'circle':
            dataLayer.add(new google.maps.Data.Feature({
              properties: {
                radius: shape.overlay.getRadius()
              },
              geometry: new google.maps.Data.Point(shape.overlay.getCenter())
            }));
            break

        }

        dataLayer.toGeoJson((obj) => {
          this.saveToDB(obj)
        });

      });
    },
    */

    async saveToDB (mapData) {
      var tempData = this.lodash.cloneDeep(this.location)
      var aMethod = 'patch'
      var aUrl = '/api/teams/' + this.team.id + '/locations/' + tempData.id

      tempData.map = mapData

      await axios({
        method: aMethod,      
        url: aUrl,
        data: tempData
      })
      .then(response => {
        this.location = response.data
        this.showSnackbar(this.$t('teams.success_location_update'), 'success')
      })

    },

    
    loadMapDrawingManager() {    
      let self = this;

      this.$refs.map.$mapPromise.then((mapObject) => {
        
        this.drawingManager = new google.maps.drawing.DrawingManager({
          drawingControl: false,
          polygonOptions: {
            draggable: true,
            clickable: true,
            editable: true
          },
          rectangleOptions: {
            draggable: true,
            clickable: true,
            editable: true
          }
        });
        

        //const drawingManager = this.$refs.dm
        this.drawingManager.setMap(this.$refs.map.$mapObject)


        google.maps.event.addListener(this.drawingManager, 'overlaycomplete', (event) => {
          var dataLayer = this.$refs.map.$mapObject.data;

          switch (event.type) {
            case 'marker':
              dataLayer.add(new google.maps.Data.Feature({
                geometry: new google.maps.Data.Point(event.overlay.getPosition())
              }));
              break

            case 'rectangle':
              var b = event.overlay.getBounds(),
                p = [b.getSouthWest(), {
                    lat: b.getSouthWest().lat(),
                    lng: b.getNorthEast().lng()
                  },
                  b.getNorthEast(), {
                    lng: b.getSouthWest().lng(),
                    lat: b.getNorthEast().lat()
                  }
                ]
              dataLayer.add(new google.maps.Data.Feature({
                geometry: new google.maps.Data.Polygon([p])
              }));
              break

            case 'polygon':
              dataLayer.add(new google.maps.Data.Feature({
                geometry: new google.maps.Data.Polygon([event.overlay.getPath().getArray()])
              }));
              break
            
            case 'polyline':
              dataLayer.add(new google.maps.Data.Feature({
                geometry: new google.maps.Data.LineString(event.overlay.getPath().getArray())
              }));
              break

            case 'circle':
              dataLayer.add(new google.maps.Data.Feature({
                properties: {
                  radius: event.overlay.getRadius()
                },
                geometry: new google.maps.Data.Point(event.overlay.getCenter())
              }));
              break

          }

          console.log(dataLayer)

          // Disable drawingManager
          this.drawingManager.setDrawingMode(null);

        });
      });
    },
  }
}
</script>

<style scoped>
.aa {
  background: #424242 !important;
}

</style>