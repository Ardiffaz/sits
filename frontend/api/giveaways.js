import axios from 'axios';

export default {
    getList({filter, pageNumber, sortParam, sortDir}) {
        let params = {};

        if (filter)
            params = Object.assign({}, params, filter);

        if (pageNumber)
            params.page = pageNumber;

        if (sortParam)
            params.sort = sortParam;

        if (sortDir)
            params.dir = sortDir;

        let queryString = Object.keys(params).map((key) => {
            return key + '=' + encodeURIComponent(params[key])
        }).join('&');

        if (queryString.length)
            queryString = '?' + queryString;

        return axios.get('/api/giveaways' + queryString);
    },
    save(giveaways) {
        return axios.post('/api/giveaways/save', {giveaways})
    },
    finish(giveawayId) {
        return axios.post('/api/giveaways/finish', {giveawayId})
    },
    remove(giveawayId) {
        return axios.post('/api/giveaways/remove', {giveawayId});
    }
}