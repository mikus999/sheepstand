<template>
  <div>
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

    <l-map 
      ref="map" 
      :center="center" 
      :zoom="zoom"
      :style="'width: ' + width + '; height: ' + height" 
      @ready="onMapReady()">

      <l-tile-layer :url="url" :attribution="attribution" />

    </l-map>
  </div>
</template>

<script>
import axios from 'axios'
import helper from '~/mixins/helper'
import L from 'leaflet'
import { latLng } from "leaflet"
import { LMap, LTileLayer, LControl } from 'vue2-leaflet'
import drawControl from 'leaflet-draw'
import * as mykey from './helpers/leaflet-providers.js'

export default {
  name: 'Leaflet',
  mixins: [helper],
  components: {
    LMap,
    LTileLayer,
    LControl,
  },
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
      map: null,
      url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
      edited: null,
      center: latLng(1, 1),
      zoom: 13,
      shapes: null,
      shapeOptions: {
        fillColor: this.fill,
        fillOpacity: 0.4,
        stroke: true,
        weight: 2,
        color: '#999999',
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
      selectedShape: null,
      isChanged: null,
      drawingManager: null,
      drawControl: null
    }
  },

  computed: {
  },

  created () {

  },

  methods: {
    onMapReady() {
      this.map = this.$refs.map.mapObject
      this.getPosition()
      this.loadDrawControl()
    },

    
    getPosition() {
      navigator.geolocation.getCurrentPosition(position => {
       this.center = latLng(position.coords.latitude, position.coords.longitude)
      }, null, this.locatorOptions)
    },

    loadDrawControl() {    
      this.drawControl = new L.Control.Draw({
        draw: false
      })

      this.map.addControl(this.drawControl)



      // Map event handlers
      this.map.on(L.Draw.Event.CREATED, (e) => {
        var type = e.type
        var layer = e.layer

        // Shape event handlers
        layer.on('click', (e) => {
          L.DomEvent.stopPropagation(e)
          console.log(e.sourceTarget.editing.enabled)
          if (e.sourceTarget.editing.enabled) {
            e.sourceTarget.editing.disable()
          } else {
            e.sourceTarget.editing.enable()
          }
        })

        this.map.addLayer(layer)
      });

      
      this.map.on('click', (e) => {
        this.clearSelection()
      })
    },


    updateMapType (type) {
      switch (type) {
        case 'roadmap':
          this.url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
          this.attribution = '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
          break
        case 'satellite':
          this.url = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
	        this.attribution = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
          break
      }
    },

    changeDrawingMode (mode) {
      switch (mode) {
        case 'polygon':
          new L.Draw.Polygon(this.map, this.shapeOptions).enable()
          break
        case 'rectangle':
          new L.Draw.Rectangle(this.map, this.shapeOptions).enable()
          break
        case 'circle':
          new L.Draw.Circle(this.map, this.shapeOptions).enable()
          break
        case 'marker':
          new L.Draw.Marker(this.map, this.shapeOptions).enable()
          break
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
      this.map.eachLayer((layer) => {
        layer.editing.disable()
      })
    },

    deleteSelection (shape) {
      if (this.selectedShape !== null) {
        var dataLayer = this.$refs.map.$mapObject.data;
        dataLayer.remove(shape)
        this.selectedShape = null
      }
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