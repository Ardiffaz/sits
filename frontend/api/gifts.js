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

        return axios.get('/api/gifts' + queryString);
    },
    reserve(giftIds, userId, isReserving) {
        return axios.post('/api/gifts/reserve', {giftIds, userId, isReserving});
    },
    remove(giftIds) {
        return axios.post('/api/gifts/remove', {giftIds});
    },
    save(gifts) {
        return axios.post('/api/gifts/save', {gifts})
    },
}