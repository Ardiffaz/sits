import axios from 'axios';

axios.defaults.timeout = 1800*1000;

export default {
    getList() {
        return axios.get('/api/users');
    },
    getGifterList() {
        return axios.get('/api/users/gifters');
    },
    getUsersWithRolesList() {
        return axios.get('/api/users/with_roles');
    },
    updateGames(userId) {
        return axios.post('/api/users/update', {userId: userId}, {timeout: 1800*1000});
    },
    changeActivity(userId, active) {
        return axios.post(`/api/users/${userId}/change_activity`, {active: active});
    },
    changeCustomName(userId, customName) {
        return axios.post(`/api/users/${userId}/change_custom_name`, {custom_name: customName});
    },
    removeUser(userId) {
        return axios.post('/api/users/remove', {userId: userId});
    },
    grantRole(userId, role) {
        return axios.post('/api/users/grant_role', {user: userId, role});
    },
    revokeRole(userId, role) {
        return axios.post('/api/users/revoke_role', {user: userId, role});
    }
}