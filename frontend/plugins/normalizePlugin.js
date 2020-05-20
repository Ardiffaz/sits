const normalizePlugin = {
    install (Vue, options) {
        Vue.prototype.$normalizeData = function (items) {
            let ids = [];
            let map = {};

            for (let itemKey in items)
            {
                if (!items.hasOwnProperty(itemKey))
                    continue;

                let item = items[itemKey];

                if (item.id)
                {
                    ids.push(item.id);
                    map[item.id] = item;
                }

            }

            return {ids, map};
        }
    }
};

export default normalizePlugin;