import axios from 'axios';

export default {
    syncGames () {
        return axios.get('/api/sync/games');
    },

    syncAppDetails (updateType) {
        if (updateType === 'dw')
            return axios.get('/api/sync/app_details_dw');
        else
            return axios.get(`/api/sync/app_details?type=${updateType}`);
    },

    syncSingleAppDetails(gameId) {
        return axios.get(`/api/sync/app_details?id=${gameId}`);
    }
}