import axios from 'axios';
import { LIST, CREATE, UPDATE, REMOVE, SUCCESS, FAILURE, REQUEST } from '../constants';

export function modelRest(m,a,d,c=function(){},f=()=>{}){
    return (dispatch) => {
        let u = `/${m}`,
            t = 'get',
            p = {};
        switch(a){
            case LIST:
                let str = (d!=undefined)?Object.keys(d).map(function(key) { return (d[key].length)? `${key}=${d[key]}`:''; }).join('&'):'';
                u = `/${m}?${str}`;
                t = 'get';
                break;
            case CREATE:
                t = 'post';
                p = d;
                break;
            case UPDATE:
                u = `/${m}/${d.id}`,
                t = 'put';
                p = d;
                break;
            case REMOVE:
                u = `/${m}/${d.id}`,
                t='delete';
                break;
        }
        dispatch({
            model: m,
            type: a,
            status: REQUEST
        });
        axios( {
            url: u,
            method: t,
            data: p
        }).then((response) => {
                c(response);
                dispatch({
                    model: m,
                    type: a,
                    status: SUCCESS,
                    payload: response
                });
            })
            .catch((error) => {
                f(error);
                dispatch({
                    model: m,
                    type: a,
                    status: FAILURE,
                    payload: error
                });
            })
    };
}

export function shellReload () {
    axios( {
        url: `/shell`,
        method: 'GET'
    }).then((response) => {
            console.log('Shell restarted', response);
        })
        .catch((error) => {
            console.warn('Shell not', error);
        })
}
