<template>
  <div class="text-white mb-8">
    <div class="places-input text-gray-800">
      <!-- Search bar -->
      <input type="search" id="input-map" class="form-control" placeholder="Search Location, Post-code..." />

      <p>Selected: <strong id="address-value">none</strong></p>
    </div>
    <div class="weather-container font-sans w-128 max-w-lg rounded-lg overflow-hidden bg-gray-900 shadow-lg mt-4">
      <div class="current-weather flex items-center justify-between px-6 py-8">
        <div class="flex items-center">
          <div>
            <div class="text-1xl font-semibold">{{ toDayOfWeek(currentTemperature.date)}} {{toDate(currentTemperature.date) }}</div>
            <div class="text-6xl font-semibold">{{ currentTemperature.actual }}&deg;C</div>
            <div>Feels Like: {{ currentTemperature.feels }}&deg;C</div>
            <div>High: {{ currentTemperature.high }}&deg;C</div>
            <div>Low: {{ currentTemperature.low }}&deg;C</div>
          </div>
          <div class="mx-5">
            <div class="font-semibold">{{ currentTemperature.summary | capitalize }}</div>
            <div>{{ location.name }}</div>
          </div>
        </div>
        <div>Humidity: {{ currentTemperature.humidity }}%</div>
        <div><img :src="`https://openweathermap.org/img/wn/${currentTemperature.icon}@2x.png`"></div>
      </div>

      <div class="future-weather text-sm bg-gray-800 px-6 py-8 overflow-hidden">
        <div 
            v-for="(day, index) in dailyThreeDays" 
            :key="day.time" 
            class="flex items-center" 
            :class="{ 'mt-8' : index > 0 }"
            >
            <div class="w-1/6 text-lg text-gray-200">{{ toDayOfWeek(day.dt) }}</div>
            <div class="w-1/6 text-sm text-gray-200">{{ toDate(day.dt) }}</div>
            <div class="w-3/6 px-4 flex items-center">
                <div><img :src="`https://openweathermap.org/img/wn/${day.weather[0].icon}.png`"></div>
                <div class="ml-3">{{ day.weather[0].description | capitalize }}</div>
            </div>
            <div class="w-1/6 text-sm text-gray-200">{{ day.humidity }}%</div>
            <div class="w-1/6 text-right">
                <div>{{ Math.round(day.temp.max) }}&deg;C</div>
                <div>{{ Math.round(day.temp.min) }}&deg;C</div>
            </div>
        </div>

      </div>
      <div id="map-example-container">

      </div>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    this.fetchData()

    const placesAutocomplete = places({
        appId: 'plEMJKXHGKZL',
        apiKey: 'dcbfdd49e960293286f055c4dfeb4c5c',
        container: document.querySelector('#input-map')
      });

    const $address = document.querySelector('#address-value')
      placesAutocomplete.on('change', (e) => {
        $address.textContent = e.suggestion.value
        this.location.name = `${e.suggestion.name}, ${e.suggestion.country}`
        this.location.lat = e.suggestion.latlng.lat
        this.location.lon = e.suggestion.latlng.lng
      });

      placesAutocomplete.on('clear', function () {
        $address.textContent = 'none';
      });
    
    const map = L.map('map-example-container', {
      scrollWheelZoom: false,
      zoomControl: false
    });

    const osmLayer = new L.TileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        minZoom: 1,
        maxZoom: 13,
        attribution: 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
        }
    );

    const markers = [];

    map.setView(new L.LatLng(0, 0), 1);
    map.addLayer(osmLayer);

    placesAutocomplete.on('suggestions', handleOnSuggestions);
    placesAutocomplete.on('cursorchanged', handleOnCursorchanged);
    placesAutocomplete.on('change', handleOnChange);
    placesAutocomplete.on('clear', handleOnClear);

    function handleOnSuggestions(e){
    markers.forEach(removeMarker);
    markers = [];

    if (e.suggestions.length === 0) {
      map.setView(new L.LatLng(0, 0), 1);
      return;
    }

    e.suggestions.forEach(addMarker);
    findBestZoom();
    }

    function handleOnChange(e) {
        markers
        .forEach(function(marker, markerIndex) {
            if (markerIndex === e.suggestionIndex) {
            markers = [marker];
            marker.setOpacity(1);
            findBestZoom();
            } else {
            removeMarker(marker);
            }
        });
    }

    function handleOnClear() {
        map.setView(new L.LatLng(0, 0), 1);
        markers.forEach(removeMarker);
    }

    function handleOnCursorchanged(e) {
    markers
      .forEach(function(marker, markerIndex) {
        if (markerIndex === e.suggestionIndex) {
          marker.setOpacity(1);
          marker.setZIndexOffset(1000);
        } else {
          marker.setZIndexOffset(0);
          marker.setOpacity(0.5);
        }
      });
    }
    function addMarker(suggestion) {
    var marker = L.marker(suggestion.latlng, {opacity: .4});
    marker.addTo(map);
    markers.push(marker);
    }

    function removeMarker(marker) {
        map.removeLayer(marker);
    }

    function findBestZoom() {
        var featureGroup = L.featureGroup(markers);
        map.fitBounds(featureGroup.getBounds().pad(0.5), {animate: false});
    }

  },
  watch: {
      location: {
          handler(newValue, oldValue) {
              this.fetchData()
          },
          deep: true
      }
  },
  computed: {
      dailyThreeDays() {
          return this.daily.filter((day, index) => index >= 1 && index <= 3 )
      }
  },
  data() {
    return {
      currentTemperature: {
        actual: "",
        feels: "",
        summary: "",
        icon: "",
        high: "",
        low: "",
        humidity: ""
      },
      daily: [],
      location: {
        name: "Tokyo, Japan",
        lat: 35.6828,
        lon: 139.759
      }
    };
  },
  methods: {
    fetchData() {
      fetch(`/api/weather?lat=${this.location.lat}&lon=${this.location.lon}`)
        .then(response => response.json())
        .then(data => {
          //   console.log(data);
          this.currentTemperature.date = data.current.dt;
          this.currentTemperature.actual = Math.round(data.current.temp);
          this.currentTemperature.feels = Math.round(data.current.feels_like);
          this.currentTemperature.summary = data.current.weather[0].description;
          this.currentTemperature.humidity = data.current.humidity;
          this.currentTemperature.icon = data.current.weather[0].icon;
          this.daily = data.daily
          this.currentTemperature.high = Math.round(data.daily[0].temp.max)
          this.currentTemperature.low = Math.round(data.daily[0].temp.min)
        });
    },
    toDayOfWeek(timestamp) {
        const newDate = new Date(timestamp*1000)
        const days = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']

        return days[newDate.getDay()]
    },
    toDate(timestamp) {
        const newDate = new Date(timestamp*1000)
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const year = newDate.getFullYear();
        const month = months[newDate.getMonth()];
        const date = newDate.getDate();
        const hour = newDate.getHours();
        const min = newDate.getMinutes();
        const sec = newDate.getSeconds();
        const time = date + ' ' + month;

        return time;
    },
  },
  filters: {
      capitalize: function (value) {
          if (!value) return ''
          value = value.toString()
          return value.charAt(0).toUpperCase() + value.slice(1)
      }
  }
};
</script>

<style>
  #map-example-container {height: 300px};
</style>
