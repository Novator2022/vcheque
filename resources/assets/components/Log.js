import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header, Label, Table } from 'semantic-ui-react';
import {Date, Time, Amount} from './simple';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,RESTORE,UPDATE} from '../constants';
import Cheque from './Cheque';
import ChequeSchedule from './ChequeSchedule/ChequeSchedule';
import User from './User';
import Organisation from './Organisation';

class Log extends Component {
    constructor(props){
        super(props);
    }
    render() {
        const {item, organisations,nomenclatures,expensetypes, contractors, users} = this.props;

        const userOptions = users.data.map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.email
            }
        })

        let description = null;

        switch( item.type ){
            case "cheque_schedule":
                description = null;//(<ChequeSchedule item={item.data.object} contractors={contractors} organisations={organisations} expensetypes={expensetypes} nomenclatures={nomenclatures} userOptions={userOptions}/>);
                break;
            case "cheque":
                let amount = 0;
                item.data.object.data.positions.map( (pos) => {
                    amount += parseFloat(pos.amount);
                } )
                description = (<Cheque item={item.data.object} organisations={organisations} nomenclatures={nomenclatures}/>);
                description = (<div style={{display: 'inline-block', marginLeft: '2em'}}>
                    <strong>#{item.data.object.id}</strong>
                    <span>Организация: <strong>{item.data.object.organisation.name || ''}</strong></span>
                    <span>Сумма чека: <Amount value={amount}/><Icon name="ruble"/></span>
                </div>)
                break;
            case "user":
                description = null;//(<User user={item.data.object} readonly/>);
                break;
        }
        return (<Item>
                <Item.Content>
                    <Item.Header></Item.Header>
                    <Item.Meta>
                        <Date value={item.created_at}/>
                        <Time value={item.created_at}/>
                        <Icon name="user"/>{item.user.name}:
                            <strong>{trans(`messages.logs.${item.data.action}_${item.type}`)}</strong>
                            {description}
                    </Item.Meta>
                    <Item.Extra></Item.Extra>
                    <Item.Description>
                        {/* <Table><Table.Body>{ description }</Table.Body></Table> */}
                    </Item.Description>

                </Item.Content>
        </Item>);
        return (<Table.Row>
                <Table.Cell>{trans(`messages.logs.${item.data.action}_${item.type}`)}</Table.Cell>
                <Table.Cell><Date value={item.created_at}/></Table.Cell>
                <Table.Cell>{item.user.name}</Table.Cell>
                <Table.Cell>{ description }</Table.Cell>
        </Table.Row>);
    }
}
// <Item.Image size='tiny' src='https://react.semantic-ui.com/images/wireframe/image.png' />
export default connect(
    null,
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(Log);
