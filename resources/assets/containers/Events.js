import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Icon, Label, Transition, Item, Popup, Message } from 'semantic-ui-react';
import {Amount} from '../components/simple';

import {paginate} from '../helpers/paginator';

class Events extends Component {
    constructor(props){
        super(props);
        this.state = {
            shaking: true,
            events: props.events.new
        };
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        const {props} = this;

        if (prevState.events != prevProps.events.new) {
            this.setState({shaking: !prevState.shaking, events: prevProps.events.new});
        }
    }

    handleStatusChanged(data) {
        this.props.action(
            'cheque',
            'STATUS',
            data,
            (response)=>{},
            (error)=>{}
        )
    }

    render() {
        const newEvents = this.props.events.new;
        const duration = 500;
        const {shaking} = this.state;
        const {events} = this.props;
        const handleViewed = (id) => {
            this.props.action(
                'event',
                'DECRIMENT',
                id,
                (response)=>{
                },
                (error)=>{
                }
            );
        }
        const handleViewedAll = () => {

            Object.keys(events.data).map((i) => {
                handleViewed(i);
            });
        }
        return <Popup
                hoverable={true}
                position="bottom left"
                size="huge"
                trigger={
                    <div className="top-events">
                        <Transition
                            animation='tada'
                            duration={duration}
                            visible={shaking}>
                            <Icon name='bell outline' size="big"/>
                        </Transition>
                        {
                            newEvents ? <Label as='a' circular color="olive" size="small" className="top-events-count">{newEvents}</Label>  : null
                        }

                  </div>
                }
            >
            <Popup.Content>
                {
                    (events.data && Object.keys(events.data) && Object.keys(events.data).length)
                    ? <Item.Group divided>
                        <Item>
                            <Item.Content>
                                <a onClick={(e) => {handleViewedAll()}}><Icon name='close' /> Закрыть все</a>
                            </Item.Content>
                        </Item>
                        {
                            Object.keys(events.data).map((i) => {
                                const item = events.data[i];
                                let title = 'Событие';
                                let content = null;
                                let meta = null;

                                switch(item.object) {
                                    case 'user':
                                        title = 'Пользователь';
                                        switch(item.action) {
                                            case 'login':
                                                title += ' вошел в систему';
                                            break;
                                        }
                                        content = `${item.data.name} ${item.data.email}`;
                                        break;
                                    case 'cheque':
                                        title = 'Чек';
                                        let amount = 0;
                                        item.data.data.positions.map( (pos) => {
                                            amount += parseFloat(pos.amount);
                                        } )
                                        content = (<div>
                                            #{item.data.id}&nbsp;
                                            <p>Организация: <strong>{item.data.organisation.name}</strong></p>
                                            <p>Сумма чека: <Amount value={amount}/><Icon name="ruble"/></p>
                                            <p>Клиент: <i>{item.data.user.name}</i> <i className="ui grey color">{item.data.user.email}</i></p>
                                        </div>);
                                        meta = <div><strong>Тип чека:</strong> {window.cheques.types[item.data.type]}</div>;
                                        switch(item.action) {
                                            case 'create':
                                            case 'new':
                                                console.log('no need bell' , item.action);
                                                handleViewed(i);
                                                return null;
                                            case 'request':
                                                title += ' создан и отправлен в обработку';
                                            break;
                                            case 'inprogress':
                                                title += ' отправлен на печать';
                                            break;
                                            case 'success':
                                                title += ' выбит';
                                            break;
                                            case 'failed':
                                                title = 'Ошибка печати чека';
                                                content = <Message negative>
                                                    {JSON.stringify(item.data.modulkassa)}
                                                </Message>
                                            break;
                                        }
                                        this.handleStatusChanged(item.data);
                                        break;
                                    case 'cheque_schedule':
                                        title = 'Чеки по расписанию';
                                        switch(item.action) {
                                            case '"processes"':
                                                title = 'Выполнен ' + title;
                                            break;
                                        }
                                        content = (<div> #{item.data.id} <strong>{item.data.organisation.name}</strong> <i>{item.data.user.name}</i></div>);
                                        meta = <div><strong>Периодичность:</strong> {window.cheques.findByValue(window.cheques.periodicOptions, item.data.periodic).value}</div>;

                                        break;
                                }


                                return  (<Item key={i}>
                                    <Item.Content>
                                        <Item.Extra>
                                            {title}
                                            <a style={{float: 'right'}} onClick={(e) => {handleViewed(i)}}><Icon name='close' /></a>
                                        </Item.Extra>
                                        <Item.Description>{content}</Item.Description>
                                        <Item.Meta>{meta}</Item.Meta>

                                    </Item.Content>
                                </Item>);
                            })
                        }
                    </Item.Group>
                    : 'Нет новых уведомлений'
                }
            </Popup.Content>
        </Popup>
    }
}
export default connect(
    (state, ownProps)=>{
        return {
            events:state.events
        };
    },
    (dispatch) => {
        return {
            action: (m,a,data,callback,failback) => {
                dispatch({
                    model: m,
                    type: a,
                    status: 'SUCCESS',
                    payload: data
                });
            }

        };
    }
)(Events);
