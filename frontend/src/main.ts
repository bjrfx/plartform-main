import 'bootstrap/dist/css/bootstrap.min.css';
import '@/assets/style.css'; // Custom CSS overrides Bootstrap if needed
//import 'bootstrap';

import {createApp} from 'vue'
import App from '@/App.vue'
import router from '@/router/index';
import {createPinia} from 'pinia';
import {useSiteDataStore} from "@/stores/siteData";
import {provideLoading} from './services/api';

const app = createApp(App);

const pinia = createPinia(); // Create Pinia instance
app.use(pinia);
app.use(router);

// Provide loading state globally
provideLoading(app);

const siteDataStore = useSiteDataStore();

// Mount the app immediately
app.mount("#app");

// Fetch site data asynchronously after mount
(async (): Promise<void> => {
    try {
        await siteDataStore.fetchSiteData();
    } catch (error) {
    }
})();