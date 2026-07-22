import { createApp } from 'vue';
import VuePdfApp from "vue3-pdf-app";
// import this to use default icons for buttons
import "vue3-pdf-app/dist/icons/main.css";

const app = createApp({
    data() {
        return {

        };
    },
    mounted() {
        // Any mounted logic can go here
    },
    components: {
        VuePdfApp
    }
});


app.mount('#app');

export default app;
