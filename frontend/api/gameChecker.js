import axios from 'axios';

export default {
    getGroupGames({groupId, searchParam, pageNumber}) {
        return axios.get(`/api/groups/${groupId}/games?q=${searchParam}&page=${pageNumber}`);
    }
}