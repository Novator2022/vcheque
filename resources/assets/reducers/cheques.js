import { COMPANY, LIST, CREATE, UPDATE, REMOVE, SUCCESS, FAILURE, REQUEST, INITIAL_STATE, STATUS } from '../constants';

export default function cheques(state = INITIAL_STATE,action) {
    if(action.model !=='cheque')return state;
    switch(action.type){
        case LIST:
            switch(action.status){
                case REQUEST: return {...state, fetching: true};
                case SUCCESS: return {...state, fetching: false, data: action.payload.data};
                case FAILURE: return {...state, fetching: false, error: action.payload};
            }

        case CREATE:
            switch(action.status){
                case REQUEST: return {...state, fetching: true};
                case SUCCESS:
                    let newList = state.data.slice().reverse();
                    newList.push(action.payload.data);
                    return {...state, fetching: false, data: newList.reverse()};
                case FAILURE: return {...state, fetching: false, error: action.payload};
            }
        case UPDATE:
            switch(action.status){
                case REQUEST: return {...state, fetching: true};
                case SUCCESS:
                    const newItem = action.payload.data;
                    let dl = [];
                    state.data.map( (item,i) => {
                        dl.push((item.id === newItem.id)?newItem:item);
                    });
                    return {...state, fetching: false, data: dl};
                case FAILURE: return {...state, fetching: false, error: action.payload};
            }
        case REMOVE:
            switch(action.status){
                case REQUEST: return {...state, fetching: true};
                case SUCCESS: {
                    const newItem = action.payload.data;
                    let dl = [];
                    state.data.map( (item,i) => {
                        if(item.id !== newItem.id)dl.push(item);
                    });
                    return {...state, fetching: false, data: dl};
                }
                case FAILURE: return {...state, fetching: false, error: action.payload};
            }
        case STATUS:
            const newItem = action.payload;
            let dl = [];
            state.data.map( (item,i) => {
                dl.push( (item.id === newItem.id) ? newItem : item);
            });
            return {...state, fetching: false, data: dl};
        default: return state;
    }
}
