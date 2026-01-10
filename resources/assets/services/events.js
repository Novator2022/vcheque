import Echo from 'laravel-echo'


export class LaravelEvents
{
    constructor(store) {
        this.store = store;
        window.io = require('socket.io-client');
        window.Echo = new Echo({
            broadcaster: 'socket.io',
            host: window.location.hostname + ':6001'
        });

        window.Echo.channel('test-event')
            .listen('TestEvent', (e) => {
                this.dispatch('TestEvent', e);

            });
            
        window.Echo.private(`channel-logs.${window.auth.id}`)
            .listen('.log', (e) => {
                console.log('laravel-echo new private event', e);
                this.dispatch('log-event', e);
            });

        window.Echo.join(`channel-logs`)
            .listen('.log', (e) => {
                console.log('laravel-echo new common admin event', e);
                this.dispatch('log-event', e);
            });

        this.dispatch = this.dispatch.bind(this);
    }

    dispatch(type, e) {
        this.store.dispatch( (dispatch) => {
            dispatch({
                model: 'event',
                type: 'BROADCAST',
                status: 'SUCCESS',
                payload: e
            });
        });
    }
}
