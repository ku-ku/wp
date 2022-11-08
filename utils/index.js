const NULL_ID = "00000000-0000-0000-0000-000000000000";
import $moment from "moment";
$moment.locale("ru");


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
    "dvslist": 8
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

const gen_days = (at = new Date() )=>{
  const d = $moment([at.getFullYear(), at.getMonth(), 1]);
  const res = [];
  while ( at.getMonth()===d.get('month') ){
      res.push( d.clone() );
      d.add(1, 'days');
  }
  return res;
};  //_gen_days


export {
    NULL_ID,
    MODES,
    DIA_MODES,
    empty,
    lookup,
    gen_days
};
