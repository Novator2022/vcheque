//global initial state
export const INITIAL_STATE = {
    model:'',
    fetching:false,
    error: null,
    data:[]
};
// models
export const COMPANY = 'company';
export const ACTIVITY = 'activity';
export const SERVICE = 'service';
export const SERVICEDETAIL = 'service-detail';
export const INVOICE = 'invoice';
export const WITHDRAWAL = 'withdrawal';

// actions on model
export const CREATE = 'CREATE';
export const UPDATE = 'UPDATE';
export const REMOVE = 'REMOVE';
export const RESTORE = 'RESTORE';
export const STATUS = 'STATUS';
export const LIST = 'LIST';
export const INFO = 'INFO';
export const BROADCAST = 'BROADCAST';

// status on actions
export const SUCCESS = 'SUCCESS';
export const FAILURE = 'FAILURE';
export const REQUEST = 'REQUEST';
export const MONTHSNAME = [
    "Январь",
    "Февраль",
    "Март",
    "Апрель",
    "Май",
    "Июнь",
    "Июль",
    "Август",
    "Сентябрь",
    "Ноябрь",
    "Декабрь"
];
export const trans = (s) => {
    const lang = window._trans?window._trans:{};
    let path = s.split(/\./), ret = lang;
    path.map( i => {
        ret = (ret[i])
            ?ret[i]
            :false;
    })
    return ret?ret:s;
}
export const USER_ROLES = [
    {
        icon: 'user outline',
        value: 'user',
        text: trans('messages.users.roles.user')
    },
    {
        icon: 'user',
        value: 'manager',
        text: trans('messages.users.roles.manager')
    },
    {
        icon: 'users',
        value: 'admin',
        text: trans('messages.users.roles.admin')
    },
    {
        icon: 'user secret',
        value: 'superadmin',
        text: trans('messages.users.roles.superadmin')
    }
];
export const USER_ROLES_SEARCH = [
    {
        icon: 'search',
        value: '',
        text: trans('messages.users.roles.all')
    },
    {
        icon: 'user outline',
        value: 'user',
        text: trans('messages.users.roles.user')
    },
    {
        icon: 'user',
        value: 'manager',
        text: trans('messages.users.roles.manager')
    },
    {
        icon: 'users',
        value: 'admin',
        text: trans('messages.users.roles.admin')
    },
    {
        icon: 'user secret',
        value: 'superadmin',
        text: trans('messages.users.roles.superadmin')
    }
];
