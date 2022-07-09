const NULL_ID = "00000000-0000-0000-0000-000000000000";

const MODES = {
    "none":     0,
    "default":  1,
    "loading":  2,
    "saving":   3,
    "success":  4,
    "search":   5,
    "error":    9
};

const DIA_MODES = {
    "none": 0,
    "action": 1,
    "reday": 2,
    "dvs": 3,
    "user": 4,
    "staff": 5
};

/**
 * @param {String} val
 * @return {Boolean}
 */
const empty = val => {
    if (!!val){
        return /^$/.test(val);
    }
    return true;
};

const lookup = async addr => {
    const _addr = 'Россия, ' + addr.replace(/((\s|\,)+ул\.?)+/gi, ' ').replace(/(г\.)+/gi, '').split(" ").join(', ');
    console.log('addr', _addr);
    return $.getJSON({
                url: `https://nominatim.openstreetmap.org/search/?q=${ _addr }&accept-language=ru&limit=1&format=json`,
                crossDomain: true,
                timeout:5000
    });
};   //lookup




export {
    NULL_ID,
    MODES,
    DIA_MODES,
    empty,
    lookup
};
