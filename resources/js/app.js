import './bootstrap';

import { createApp } from 'vue';
import Pricing from './components/Pricing.vue';

const app = createApp();

app.component('pricing-component', Pricing);

app.mount('#app');