import { fetchWithErrorHandling } from './modules/errorHandler.js';
console.log('start');
async function init() {
  try {
    const data = await fetchWithErrorHandling('/test');
    console.log('App data loaded', data);
    // further app initialization here
  } catch (err) {
    console.error('Init error', err);
  }
}

init();
