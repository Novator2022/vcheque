import { COMPANY, LIST, CREATE, UPDATE, REMOVE, SUCCESS, FAILURE, REQUEST, BROADCAST } from '../constants';

const INITIAL_STATE = {
    new: 0,
    data: {}
};

export default function events(state = INITIAL_STATE, action) {

    if (action.model !== 'event') return state;

    switch(action.type){
        case BROADCAST:

            const data = state.data;
            const payload = action.payload;
            const id = `${payload.object || 'object'}_${payload.action}_${payload.data.id }`;
            data[id] = payload;

            return {...state, new: ++state.new, data: data};

        case 'DECRIMENT':
            let events = state.data;
            delete events[action.payload];

            return {...state, new: Object.keys(events).length, data: events };

        default: return state;
    }
}
