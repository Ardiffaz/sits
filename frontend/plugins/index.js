import Vue from 'vue';

import timePlugin from './timePlugin';
import debouncePlugin from './debouncePlugin';
import normalizePlugin from './normalizePlugin';

Vue.use(timePlugin);
Vue.use(debouncePlugin);
Vue.use(normalizePlugin);