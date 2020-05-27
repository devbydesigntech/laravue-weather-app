require("./bootstrap");

window.Vue = require("vue");

Vue.component("weather-app", require("./components/WeatherApp.vue").default);

const app = new Vue({
  el: "#app"
});
