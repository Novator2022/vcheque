import { combineReducers } from 'redux';

import cheques from './cheques';
import cheque_schedule from './cheque_schedules';
import contractors from './contractors';
import expensetypes from './expensetypes';
import events from './events';
import logs from './logs';
import nomenclatures from './nomenclatures';
import categories from './categories';
import organisations from './organisations';
import supports from './supports';
import users from './users';

const rootReducer = combineReducers({
    contractors,
    cheques,
    cheque_schedule,
    expensetypes,
    events,
    logs,
    nomenclatures,
    categories,
    organisations,
    supports,
    users
});

export default rootReducer;
