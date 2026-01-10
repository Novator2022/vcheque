import React from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';
import { createStore, applyMiddleware } from 'redux'
import { createLogger } from 'redux-logger'
import thunk from 'redux-thunk'
import 'semantic-ui-css/semantic.min.css'

import {LaravelEvents} from './services/events';

import rootReducer from './reducers';
import { modelRest } from './actions';
import Layout from './containers/Layout';

const store = createStore(rootReducer,applyMiddleware(thunk));
store.dispatch( modelRest('user', 'LIST') );
store.dispatch( modelRest('expensetype', 'LIST') );
store.dispatch( modelRest('organisation', 'LIST') );

store.dispatch( modelRest('log', 'LIST') );
store.dispatch( modelRest('support', 'LIST') );
store.dispatch( modelRest('cheque_schedule', 'LIST') );
render( <Provider store={store}><Layout/></Provider>, document.getElementById('app') );

const lEvents = new LaravelEvents(store);
