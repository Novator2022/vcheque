import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header, Label, Table } from 'semantic-ui-react';
import numeral from 'numeral';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,RESTORE,UPDATE} from '../constants';
import {Date} from './simple';

class Contractor extends Component {
    constructor(props){
        super(props);
        this.state={
            item: this.props.item,
            errors: false,
            editing:false
        }
    }
    handleFieldChange = (e, {name,value}) => {
        let {item} = this.state;
        item[name] = value;
        this.setState({item:item});
    }
    updateContractor = () => {
        const {item} = this.state;
        let newItem = {};
        for(let i in item){
            const val = item[i];
            if(typeof(val)!="object")newItem[i]=val;
        }
        this.props.action(
            'contractor',
            UPDATE,
            item,
            (response)=>{
                // this.setState({editing:false,errors:false});
            },
            (error)=>{
                // this.setState({errors:error.response.data});
            }
        );
    }
    restoreContractor = () => {
        const {item} = this.state;
        this.props.action(
            'contractor',
            UPDATE,
            {
                id: item.id,
                restore: true
            },
            (response)=>{
            },
            (error)=>{
            }
        );
    }
    handleRestore = () => {
        const {item} = this.state;
        this.props.action(
            'contractor',
            UPDATE,
            {
                id: item.id,
                restore: true
            },
            (response)=>{
            },
            (error)=>{
            }
        );
    }
    removeContractor = () => {
        const {item} = this.state;
        this.props.action(
            'contractor',
            REMOVE,
            item,
            (response)=>{
            },
            (error)=>{
            }
        );
    }
    render() {
        const {users} = this.props;
        const {item} = this.state;
        const {editing,errors} = this.state;
        const handleEditing = (e)=>{
            this.setState({editing:!this.state.editing});
        }
        const userOptions = (users.fetching)?[]:users.data.map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name
            }
        });
        return editing
            ?<Item><Item.Content>
                <Item.Header>#<small>{item.id}</small>{item.name}</Item.Header>
                <Item.Description>
                    <Form>
                        <Form.Group>
                            <Form.Select loading={users.fetching} name="user_id" error={(errors && errors.user_id)?{content:errors.user_id[0],pointing:'below'}:null} options={userOptions} value={item.user_id} label={trans('messages.contractors.user')} onChange={this.handleFieldChange}/>
                            <Form.Input name="name" error={(errors && errors.name)?{content:errors.name[0],pointing:'below'}:null} defaultValue={item.name || ''} label={trans('messages.name')} onChange={this.handleFieldChange}/>
                            <Form.Input name="inn" defaultValue={item.inn} label={trans('messages.contractors.inn')} onChange={this.handleFieldChange}/>
                            <Form.Input name="kpp" defaultValue={item.kpp} label={trans('messages.contractors.kpp')} onChange={this.handleFieldChange}/>
                            <Form.Input name="address" defaultValue={item.address} label={trans('messages.contractors.address')} onChange={this.handleFieldChange}/>
                            <Form.Input name="percent" defaultValue={item.percent} label={trans('messages.contractors.percent')} onChange={this.handleFieldChange}/>
                        </Form.Group>
                    </Form>
                </Item.Description>
                <Item.Extra>
                    <Menu secondary stackable borderless>
                        { item.deleted_at
                            ?<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="grey" icon onClick={this.handleRestore}><Icon name="checkmark"/>{trans('messages.activate')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                            :<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="gray" icon  onClick={handleEditing}><Icon name="undo"/>{trans('messages.cancel')}</Button>
                                </Menu.Item>
                                <Menu.Item>
                                    <Button color="green" icon  onClick={this.updateContractor}><Icon name="pencil"/>{trans('messages.save')}</Button>
                                </Menu.Item>
                                <Menu.Item>
                                    <Button color="red" icon onClick={this.removeContractor}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                        }
                    </Menu>
                </Item.Extra>
            </Item.Content></Item>
            :(<Table.Row>
                <Table.Cell>{item.id}</Table.Cell>
                <Table.Cell><Date value={item.created_at}/></Table.Cell>
                <Table.Cell>{item.user ? item.user.name: ''}</Table.Cell>
                <Table.Cell>{item.name}</Table.Cell>
                <Table.Cell>{item.inn}</Table.Cell>
                <Table.Cell>{item.kpp}</Table.Cell>
                <Table.Cell>{item.address}</Table.Cell>
                <Table.Cell>{item.percent}</Table.Cell>
                <Table.Cell>
                    <Menu secondary>
                        { item.deleted_at
                            ?<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="grey" icon onClick={this.restoreContractor}><Icon name="checkmark"/>{trans('messages.activate')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                            :<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="green" icon onClick={handleEditing}><Icon name="pencil"/>{trans('messages.edit')}</Button>
                                </Menu.Item>
                                <Menu.Item>
                                    <Button color="red" icon onClick={this.removeContractor}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                        }
                    </Menu>
                </Table.Cell>
            </Table.Row>)
        ;
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
)(Contractor);
