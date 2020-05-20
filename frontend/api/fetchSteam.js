import axios from 'axios';

export default {
    group($groupId) {
        return axios.get(`/api/fetch_steam/group/${$groupId}`);
    },

    userGroups($userSteamId) {
        return axios.get(`/api/fetch_steam/user/${$userSteamId}/groups`);
    }
}