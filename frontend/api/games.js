import axios from 'axios';

export default {
    findGames(searchParam, pageNumber) {
        return axios.get(`/api/games?q=${searchParam}&page=${pageNumber}`);
    }
}