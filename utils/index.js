const NULL_ID = "00000000-0000-0000-0000-000000000000";
import $moment from "moment";
$moment.locale("ru");
window["$moment"] = $moment;


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
    "staff": 5,
    "emp": 6,
    "emplist": 7,
    "dvslist": 8,
    "movereds": 9,
    "place": 10
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

const gen_days = (at = new Date(), end = null )=>{
  const d = (end) ? $moment(at) : $moment([at.getFullYear(), at.getMonth(), 1]);
  const res = [];
  if (end){
    var end = $moment(end);
    while ( d.isBefore(end) ){
        res.push( d.clone() );
        d.add(1, 'days');
    }
  } else {
    while ( at.getMonth()===d.get('month') ){
        res.push( d.clone() );
        d.add(1, 'days');
    }
  }  
  return res;
};  //_gen_days



const shorting = name => {
    if ( empty(name) ){
        return "";
    }
    var s;
    try {
        const a = name.trim().split(/\s{1,}/g);
        a.forEach( (w, n) => {
            s = (n===0) ? w + " " : s + w.charAt(0) + ".";
        });
    } catch(e){
        s = name;
    }
    return s;
};


export {
    NULL_ID,
    MODES,
    DIA_MODES,
    empty,
    lookup,
    gen_days,
    shorting
};
