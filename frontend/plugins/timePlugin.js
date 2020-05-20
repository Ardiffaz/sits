import moment from 'moment';

const timePlugin = {
    install(Vue, options) {
        Vue.prototype.$getCurrentTime = function() {
            return moment().format('LTS');
        };

        Vue.prototype.$getExactDateTime = function (datetime) {
            if (isNaN(datetime) || !datetime)
                return 'never';

            return moment.unix(datetime).format('lll');
        };

        Vue.prototype.$getRelativeDateTime = function (datetime) {
            if (isNaN(datetime) || !datetime)
                return 'never';

            return moment.unix(datetime).fromNow();
        };

        Vue.prototype.$getDate = function(datetime) {
            if (!datetime)
                return 'never';

            return moment(datetime).format('LL');
        };

        Vue.prototype.$getHTMLFormatDate = function (datetime) {
            if (!datetime)
                return '';

            return moment(datetime).format('YYYY-MM-DD');
        }
    }
};

export default timePlugin;