import axios from 'axios';


export default {
    getList(withMembers = false) {
        return (withMembers) ? axios.get('/api/groups?with_members=1') : axios.get('/api/groups');
    },
    getGroup(groupId) {
        return axios.get(`/api/groups/${groupId}`);
    },
    addGroup(groupSteamId) {
        return axios.post('/api/groups/add', {groupSteamId});
    },
    updateGroup(groupId) {
        return axios.post('/api/groups/update', {groupId});
    },
    changeSgLink(groupId, sgLink) {
        return axios.post(`/api/groups/${groupId}/change_sg_link`, {sgLink: sgLink});
    },
    removeGroup(groupId) {
        return axios.post('/api/groups/remove', {groupId});
    }

}