import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Table } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,UPDATE} from '../constants';

class User extends Component {
    constructor(props){
        super(props);
        this.state={
            user: this.props.user,
            errors: false,
            editing:false
        }
    }
    handleFieldChange = (e, {name,value}) => {
        let {user} = this.state;
        user[name] = value;
        this.setState({user:user});
    }
    updateUser = () => {
        const {user} = this.state;
        this.props.action(
            'user',
            UPDATE,
            user,
            (response)=>{
                // this.setState({editing:false,errors:false});
            },
            (error)=>{
                // this.setState({errors:error.response.data});
            }
        );
    }
    removeUser = () => {
        const {user} = this.state;
        this.props.action(
            'user',
            REMOVE,
            user,
            (response)=>{
            },
            (error)=>{
            }
        );
    }
    render() {
        const readonly = (this.props.readonly)?true:false;
        console
        const {user} = this.state;
        const {editing,errors} = this.state;
        const handleEditing = (e)=>{
            this.setState({editing:!this.state.editing});
        }

        return editing
            ?<Table.Row>
                <Table.Cell colspan={7}>
                    <Item>
                        <Item.Content>
                            <Item.Header>#<small>{user.id}</small>{user.name}</Item.Header>
                            <Item.Description>
                                <Form>
                                    <Form.Group>
                                        <Form.Input name="name" error={(errors && errors.name)?{content:errors.name[0],pointing:'below'}:null} defaultValue={user.name} label={trans('messages.users.name')} onChange={this.handleFieldChange}/>
                                        <Form.Select name="role" error={(errors && errors.role)?{content:errors.role[0],pointing:'below'}:null} options={USER_ROLES} value={user.role} label={trans('messages.users.role')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="email" error={(errors && errors.email)?{content:errors.email[0],pointing:'below'}:null} defaultValue={user.email} label={trans('messages.users.email')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="phone" error={(errors && errors.phone)?{content:errors.phone[0],pointing:'below'}:null} defaultValue={user.phone} label={trans('messages.users.phone')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="procent" error={(errors && errors.procent)?{content:errors.procent[0],pointing:'below'}:null} defaultValue={user.procent} label='Индивидуальная ставка' onChange={this.handleFieldChange}/>
                                        <Form.Input name="password" error={(errors && errors.password)?{content:errors.password[0],pointing:'below'}:null} type="password" label={trans('messages.users.password')} onChange={this.handleFieldChange}/>
                                    </Form.Group>

                                    {user.role == 'user' ?
                                        <Form.Group>
                                            <Form.Input name="limit_current" defaultValue={user.limit_current} label='Выбито на сумму' onChange={this.handleFieldChange} error={user.limit_current >= user.limit_total ? true : null}/>
                                            
                                            <div className="field"><label>&nbsp;</label><div className="ui input" style={{'line-height': '32px'}}>из</div></div>

                                            <Form.Input name="limit_total" defaultValue={user.limit_total} label='&nbsp;' onChange={this.handleFieldChange}/>
                                        </Form.Group>
                                        :null
                                    }
                                </Form>
                            </Item.Description>
                            <Item.Extra>
                                <Menu secondary stackable borderless>
                                    <Menu.Menu position="right">
                                        <Menu.Item>
                                            <Button color="green" icon  onClick={this.updateUser}><Icon name="pencil"/>{trans('messages.users.save')}</Button>
                                        </Menu.Item>
                                        <Menu.Item>
                                            <Button color="red" icon onClick={this.removeUser}><Icon name="x"/>{trans('messages.users.remove')}</Button>
                                        </Menu.Item>
                                    </Menu.Menu>
                                </Menu>
                            </Item.Extra>
                        </Item.Content>
                    </Item>
                </Table.Cell>
            </Table.Row>
            :<Table.Row>
                <Table.Cell>{user.id}</Table.Cell>
                <Table.Cell>{user.name}</Table.Cell>
                <Table.Cell>{ trans(`messages.users.roles.${user.role}`)}</Table.Cell>
                <Table.Cell><Icon name="phone"/>{user.phone || '-'} <Icon name="mail"/>{user.email || '-'}</Table.Cell>
                <Table.Cell>{user.procent || 'по умолчанию'}</Table.Cell>
                <Table.Cell>
                    {
                        readonly
                        ?null
                        :<Menu secondary stackable borderless>
                            <Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="green" icon onClick={handleEditing}><Icon name="pencil"/>{trans('messages.users.edit')}</Button>
                                </Menu.Item>
                                <Menu.Item>
                                    <Button color="red" icon onClick={this.removeUser}><Icon name="x"/>{trans('messages.users.remove')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                        </Menu>
                    }
                </Table.Cell>
            </Table.Row>
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
)(User);
