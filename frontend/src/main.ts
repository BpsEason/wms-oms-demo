import './assets/base.css'; // 確保基礎樣式被導入
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { createPinia } from 'pinia';

// 假設 Tailwind CSS 在主專案中已經設置
// import './index.css'; 

const app = createApp(App);
app.use(createPinia());
app.use(router);
app.mount('#app');
