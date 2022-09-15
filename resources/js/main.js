require('./app');

window.Utils = {
    toInt(val, radix) {
        radix = (radix === undefined ? 10 : radix);
        let result = parseInt(val, radix);
        return (isNaN(result) ? null : result);
    },
    toFloat(val) {
        let result = parseFloat(val);

        if (isNaN(result) || !isFinite(result))
            return null;

        return result;
    },
    refresh() {
        return location.reload(true);
    },
    getRandomInt(max) {
        return Math.floor(Math.random() * Math.floor(max));
    },

    remove(array, element) {
        const index = array.indexOf(element);

        if (index !== -1)
            array.splice(index, 1);
    }
};
