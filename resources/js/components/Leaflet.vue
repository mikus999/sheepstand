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
        <v-btn @click="getPosition()">
          <v-icon>mdi-crosshairs-gps</v-icon>
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
        <v-btn @click="changeDrawingMode('circle')" :input-value="isActiveButton('circle')">
          <v-icon>mdi-vector-circle</v-icon>
        </v-btn>
      </v-item-group>

      <v-spacer></v-spacer>

      <v-item-group class="v-btn-toggle" right>
        <v-btn @click="deleteSelection()">
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
import 'leaflet/dist/leaflet.css'
import L, { latLng } from 'leaflet'
import { LMap, LTileLayer, LControl, LMarker } from 'vue2-leaflet'
import drawControl from 'leaflet-draw'

delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
  iconRetinaUrl: '/images/leaflet/marker-icon-2x.png',
  iconUrl: '/images/leaflet/marker-icon.png',
  shadowUrl: '/images/leaflet/marker-shadow.png'
});

export default {
  name: 'Leaflet',
  mixins: [helper],
  components: {
    LMap,
    LTileLayer,
    LControl,
    LMarker
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
      shapeOptions: {
        fillColor: this.fill,
        fillOpacity: 0.4,
        stroke: true,
        weight: 2,
        color: '#999999',
        editable: true,
        clickable: true,
        bubblingMouseEvents: false
      },
      markerOptions: {
        draggable: true,
        clickable: true,
        bubblingMouseEvents: false
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
      shapes: null,
      drawControl: null,
      drawMode: null,
      drawFeature: null,
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

    recenterMap() {
      this.map.fitBounds(this.shapes.getBounds())
    },


    loadDrawControl() {    
      this.drawControl = new L.Control.Draw({
        draw: false
      })
      this.map.addControl(this.drawControl)


      this.shapes = new L.FeatureGroup()
      this.map.addLayer(this.shapes)


      // Map event handlers (mouse)
      this.map.on(L.Draw.Event.CREATED, (e) => {
        var type = e.layerType
        var layer = e.layer
        var feature = layer.feature = layer.feature || {}
        feature.type = "Feature"
        feature.properties = feature.properties || {}
        feature.properties["shape"] = type

        if (type == 'circle') {
          feature.properties["radius"] = layer.getRadius()
        }

        // Shape event handlers
        this.setShapeEvents(layer)

        this.shapes.addLayer(layer)
        this.drawMode = null
      });

      
      this.map.on('click', (e) => {
        this.clearSelection()
      })


      // Map event handlers (keyboard)
      document.addEventListener('keydown', (event) => {
        switch (event.key) {
          case 'Delete': 
          case 'Backspace':
            this.deleteSelection()
            break

          case 'Escape':
            this.clearSelection()
            break
        }
      })


      // Load existing features
      this.loadGeoJSON()
    },


    setShapeEvents(shape) {
      shape.on('click', (e) => {
        L.DomEvent.stopPropagation(e)
        this.setSelection(e.sourceTarget)
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
      if (this.drawMode === mode) {
        this.drawMode = null
        this.drawFeature.disable()
        this.drawFeature = null
      } else {
        if (this.drawFeature !== null) {
          this.drawFeature.disable()
          this.drawFeature = null
        }
        switch (mode) {
          case 'polygon':
            this.drawFeature = new L.Draw.Polygon(this.map, this.shapeOptions)
            break
          case 'rectangle':
            this.drawFeature = new L.Draw.Rectangle(this.map, this.shapeOptions)
            break
          case 'circle':
            this.drawFeature = new L.Draw.Circle(this.map, this.shapeOptions)
            break
          case 'marker':
            this.drawFeature = new L.Draw.Marker(this.map, this.markerOptions)
            break
        }

        this.drawMode = mode
        this.drawFeature.enable()
      }

    },

    isActiveButton (mode) {
      var isActive = null
      if (this.drawMode !== null) {
        if (this.drawMode === mode) {
          isActive = true
        }
      }
      return isActive
    },

    revertChanges () {
      this.shapes.clearLayers()

      // Then, load features again
      this.loadGeoJSON()
    },

    loadGeoJSON() {
     
      if (this.location.map !== null) {
        var jsonData = JSON.parse(this.location.map)
        var features = L.geoJson(jsonData, {
            style: this.shapeOptions,
            pointToLayer: (feature, latlng) => {
              if (feature.properties.radius) {
                return new L.Circle(latlng, feature.properties.radius);
              } else {
                return new L.Marker(latlng);
              }
            },
            onEachFeature: this.loadFeature,
        })
      }
      console.log(jsonData)

      this.recenterMap()
      this.isChanged = null
    },

    loadFeature(feature, layer) {
      if (feature.properties.shape === 'rectangle') {
        layer = new L.Rectangle(layer.getLatLngs())
      }
      this.setShapeEvents(layer)
      this.shapes.addLayer(layer)
    },


    saveShapes () {
      this.saveToDB()
    },

    async saveToDB () {
      var tempData = this.lodash.cloneDeep(this.location)
      var aMethod = 'patch'
      var aUrl = '/api/teams/' + this.team.id + '/locations/' + tempData.id

      var mapStr = null
      if (this.shapes.getLayers().length > 0) {
        mapStr = JSON.stringify(this.shapes.toGeoJSON())
      }

      this.location.map = mapStr
      tempData.map = mapStr

      await axios({
        method: aMethod,      
        url: aUrl,
        data: tempData
      })
      .then(response => {
        this.recenterMap()
        this.showSnackbar(this.$t('teams.success_location_update'), 'success')
        this.isChanged = null
      })

    },

    setSelection (shape) {
      this.clearSelection()

      if (shape.editing.enabled()) {
        this.selectedShape = null
        shape.editing.disable()
      } else {
        this.selectedShape = shape
        shape.editing.enable()
      }
    },

    clearSelection () {
      this.shapes.eachLayer((layer) => {
        layer.editing.disable()
      })
      this.selectedShape = null
      this.drawMode = null
    },

    deleteSelection () {
      if (this.selectedShape !== null) {
        this.shapes.removeLayer(this.selectedShape)
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