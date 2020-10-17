<template>

      <gmap-map 
        ref="map" 
        :map-type-id="mapType"
        :center="mapCenter" 
        :zoom="13" 
        :style="'width: ' + width + '; height: ' + height" 
        :options="{
          zoomControl: true,
          mapTypeControl: false,
          scaleControl: false,
          streetViewControl: false,
          rotateControl: false,
          fullscreenControl: false,
          disableDefaultUi: false,
          draggable: true,
          draggableCursor: 'default',
          gestureHandling: 'cooperative'
        }"
        >
        
          <template #visible v-if="!readonly">
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
                <v-btn @click="changeDrawingMode('marker')" :input-value="isActiveButton('marker')">
                  <v-icon>mdi-map-marker</v-icon>
                </v-btn>
                <v-btn @click="changeDrawingMode('rectangle')" :input-value="isActiveButton('rectangle')">
                  <v-icon>mdi-vector-rectangle</v-icon>
                </v-btn>
                <v-btn @click="changeDrawingMode('polygon')" :input-value="isActiveButton('polygon')">
                  <v-icon>mdi-vector-polygon</v-icon>
                </v-btn>
              </v-item-group>

              <v-spacer></v-spacer>

              <v-item-group class="v-btn-toggle" right>
                <v-btn @click="deleteSelection(selectedShape)">
                  <v-icon>mdi-delete</v-icon>
                </v-btn>
                <v-btn @click="saveShapes()" :input-value="isChanged" active-class="save-btn">
                  <v-icon>mdi-content-save</v-icon>
                </v-btn>
                <v-btn @click="revertChanges()">
                  <v-icon>mdi-undo</v-icon>
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
      default: '500px'
    },
    height: {
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
      drawingManager: null,
      selectedShape: null,
      isChanged: null,
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
      this.clearSelection()

      if (this.drawingManager.drawingMode !== mode) {
        this.drawingManager.setDrawingMode(mode)
      } else {
        this.drawingManager.setDrawingMode(null)
      }
    },

    isActiveButton (mode) {
      var isActive = null

      if (this.drawingManager !== null) {
        if (this.drawingManager.drawingMode === mode) {
          isActive = true
        }
      }

      return isActive
    },

    revertChanges () {
      var dataLayer = this.$refs.map.$mapObject.data
      
      // First, remove all current features from the map
      dataLayer.forEach(function(feature) {
        dataLayer.remove(feature);
      });

      // Then, load features again
      this.loadGeoJSON()

      // Finally, reset the save button
      this.isChanged = null
    },

    loadGeoJSON() {
      var dataLayer = this.$refs.map.$mapObject.data
      dataLayer.setStyle({
        fillColor: this.fill,
        fillOpacity: 0.4,
        editable: false,
        clickable: true,
        draggable: false
      })
      
      if (this.location.map !== null) {
        var jsonData = JSON.parse(this.location.map)
        var features = dataLayer.addGeoJson(jsonData)

        this.recenterMap(features)
      }

      this.isChanged = null
    },

    recenterMap (features) {
      if (features.length > 0) {
        var bounds = new google.maps.LatLngBounds()
        features.forEach((feature) => {
          var geo = feature.getGeometry()
          geo.forEachLatLng((latlng) => {
            var lat = latlng.lat()
            var lng = latlng.lng()

            var points = new google.maps.LatLng(lat, lng);
            bounds.extend(points);
          })
        })
        this.$refs.map.$mapObject.fitBounds(bounds)
      }
    },

    saveShapes () {
      var dataLayer = this.$refs.map.$mapObject.data

      dataLayer.toGeoJson((obj) => {
        this.saveToDB(obj)
      });


    },

    async saveToDB (mapData) {
      var tempData = this.lodash.cloneDeep(this.location)
      var aMethod = 'patch'
      var aUrl = '/api/teams/' + this.team.id + '/locations/' + tempData.id

      var mapStr = null
      if (mapData.features.length > 0) {
        mapStr = JSON.stringify(mapData)
      }

      this.location.map = mapStr
      tempData.map = mapStr

      await axios({
        method: aMethod,      
        url: aUrl,
        data: tempData
      })
      .then(response => {
        var features = new google.maps.Data().addGeoJson(mapData)
        this.recenterMap(features)
        this.showSnackbar(this.$t('teams.success_location_update'), 'success')
        this.isChanged = null
      })

    },

    setSelection (shape) {
      var dataLayer = this.$refs.map.$mapObject.data;
      dataLayer.revertStyle();
      dataLayer.overrideStyle(shape, {
        editable: true,
        draggable: true
      })
      this.selectedShape = shape
    },

    clearSelection () {
      var dataLayer = this.$refs.map.$mapObject.data;
      dataLayer.revertStyle() // Deselect all objects
      this.selectedShape = null
    },

    deleteSelection (shape) {
      if (this.selectedShape !== null) {
        var dataLayer = this.$refs.map.$mapObject.data;
        dataLayer.remove(shape)
        this.selectedShape = null
      }
    },

    
    loadMapDrawingManager() {    

      this.$refs.map.$mapPromise.then((mapObject) => {
        var dataLayer = this.$refs.map.$mapObject.data;

        // Setup the drawing manager, but hide the controls as we will use a custom toolbar
        this.drawingManager = new google.maps.drawing.DrawingManager({
          drawingControl: false,
          polygonOptions: {
            fillColor: this.fill,
            fillOpacity: 0.4,
            draggable: false,
            clickable: true,
            editable: false
          },
          rectangleOptions: {
            fillColor: this.fill,
            fillOpacity: 0.4,
            draggable: false,
            clickable: true,
            editable: false
          }
        });

        this.drawingManager.setMap(this.$refs.map.$mapObject)


        // Add a listener that fires when a new shape is drawn
        google.maps.event.addListener(this.drawingManager, 'overlaycomplete', (event) => {
          var dataLayer = this.$refs.map.$mapObject.data;
          var newFeature = null

          switch (event.type) {
            case 'marker':
              newFeature = new google.maps.Data.Feature({
                geometry: new google.maps.Data.Point(event.overlay.getPosition())
              });
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
              newFeature = new google.maps.Data.Feature({
                geometry: new google.maps.Data.Polygon([p])
              });
              break

            case 'polygon':
              newFeature = new google.maps.Data.Feature({
                geometry: new google.maps.Data.Polygon([event.overlay.getPath().getArray()])
              });
              break
            
            /*
            case 'polyline':
              newFeature = new google.maps.Data.Feature({
                geometry: new google.maps.Data.LineString(event.overlay.getPath().getArray())
              });
              break

            case 'circle':
              newFeature = new google.maps.Data.Feature({
                properties: {
                  radius: event.overlay.getRadius()
                },
                geometry: new google.maps.Data.Point(event.overlay.getCenter())
              });
              break
              */
          }

          dataLayer.add(newFeature)



          // Remove the overlay after adding the new shape to the data layer
          //   If this is not done, the shape will be duplicated on the map
          event.overlay.setMap(null)
          
          // Disable drawingManager
          this.drawingManager.setDrawingMode(null);
        })
        

        // Add listeners to the data layer
        dataLayer.addListener('rightclick', (event) => {
          this.deleteSelection(event.feature)
        });

        dataLayer.addListener('click', (event) => {
          this.setSelection(event.feature)
        });

        dataLayer.addListener('setgeometry', (event) => {
          this.isChanged = true
        });

        dataLayer.addListener('addfeature', (event) => {
          this.isChanged = true
        });

        dataLayer.addListener('removefeature', (event) => {
          this.isChanged = true
        });



        // Event that fires when map is clicked
        mapObject.addListener('click', (event) => {
          this.clearSelection()
        })

        document.addEventListener('keydown', (event) => {
          switch (event.key) {
            case 'Delete', 'Backspace':
              if (this.selectedShape !== null) {
                this.deleteSelection(this.selectedShape)
              }
              break

            case 'Escape':
              this.drawingManager.setDrawingMode(null)
              this.clearSelection()
              break
          }
        })

        // Load existing features
        this.loadGeoJSON()

      });
    },

  }
}
</script>

<style scoped>
.aa {
  background: #424242 !important;
}

@keyframes blink {
    0% { background-color: rgba(255,0,0,1) }
    50% { background-color: rgba(255,0,0,0.5) }
    100% { background-color: rgba(255,0,0,1) }
}
@-webkit-keyframes blink {
    0% { background-color: rgba(255,0,0,1) }
    50% { background-color: rgba(255,0,0,0.5) }
    100% { background-color: rgba(255,0,0,1) }
}

 .save-btn {
    -moz-transition:all 0.5s ease-in-out;
    -webkit-transition:all 0.5s ease-in-out;
    -o-transition:all 0.5s ease-in-out;
    -ms-transition:all 0.5s ease-in-out;
    transition:all 0.5s ease-in-out;
    -moz-animation:blink normal 1.5s infinite ease-in-out;
    /* Firefox */
    -webkit-animation:blink normal 1.5s infinite ease-in-out;
    /* Webkit */
    -ms-animation:blink normal 1.5s infinite ease-in-out;
    /* IE */
    animation:blink normal 1.5s infinite ease-in-out;
    /* Opera */
}

</style>