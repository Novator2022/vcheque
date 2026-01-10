import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal, Search, Table } from 'semantic-ui-react';
import InputMask from 'react-input-mask';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import User from '../components/User';

class Users extends Component {
    constructor(props){
        super(props);
        this.state = {
            useraddopen:false,
            errors:false,
            search:{},
            newuser:{
                phone: '+7 '
            }
        };
        this.closeUserAdd = this.closeUserAdd.bind(this);
    }
    // shouldComponentUpdate = (nextProps, nextState) => {
    //     return ( JSON.stringify(this.props.users) !== JSON.stringify(nextProps.users) );
    // }
    handleAddUserFieldChange = (e, {name,value}) => {
        let {newuser,errors} = this.state;
        switch(name){
            case 'phone':
                const phoneTest = /^\+7\s?([\s0-9]){1,13}$/ig;
                if(value.length == 1 && value[0]!='+') value = '+7 ';
                if(phoneTest.test(value)){
                    if(value.length == 2 ) value += ' ';
                    if(value.length == 6 ) value += ' ';
                    if(value.length == 10 ) value += ' ';
                    if(value.length == 13) value += ' ';
                    errors = false;
                    newuser[name] = value;
                }
            break;
            case 'email':
                const emailTest = /^([\.\_\-a-z\d]+)?@([\_\-a-z]+)?\.([0-9a-z]){2,}$/ig;
                if(emailTest.test(value)){
                    newuser[name] = value;
                    errors = false;
                }
                else{
                    errors = {
                        email:['Проверьте корректность заполнения email']
                    }
                }
            break;
            default:
                newuser[name] = value;
            break;
        }

        this.setState({newuser:newuser,errors: errors});
    }
    closeUserAdd = () => {
        this.setState({
            useraddopen:false,
            errors:false,
            newuser:{}
        });
    }
    addUser = (e,data) => {
        const {newuser} = this.state;
        const {closeUserAdd} = this;
        this.props.action(
            'user',
            CREATE,
            newuser,
            (response)=>{
                closeUserAdd()
            },
            (error)=>{
                this.setState({
                    errors:error.response.data
                })
            }
        );
    }
    handleSearch = (e,{name,value})=>{
        let {search} = this.state;
        search[name] = value;
        this.setState({search: search},
            ()=>{
                this.props.action(
                    'user',
                    LIST,
                    search,
                    (response)=>{},
                    (error)=>{}
                )
            }
        );
        ;
    }
    render() {
        const {users} = this.props;
        const {useraddopen,errors,search} = this.state;
        return <Segment>

            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.users.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                    <Menu.Item>
                        <Button icon primary onClick={()=>{this.setState({useraddopen:true})}}>
                            <Icon.Group>
                                <Icon name="user"></Icon>
                                <Icon corner="top right" name="plus"></Icon>
                            </Icon.Group>
                            <Icon name="plus"></Icon>
                        </Button>
                        <Modal closeIcon={<Icon name="close" onClick={()=>{this.setState({useraddopen:false})}}/>} open={useraddopen}>
                            <Header icon='user plus' content={trans('messages.users.add')} />
                            <Modal.Content>
                                <Form>
                                    <Form.Input required name="name"  label={trans('messages.users.name')} onChange={this.handleAddUserFieldChange}/>
                                    <Form.Select required name="role" error={(errors && errors.role)?{content:errors.role[0],pointing:'below'}:null} options={USER_ROLES} selected='user' label={trans('messages.users.role')} onChange={this.handleAddUserFieldChange}/>
                                    <Form.Input required name="email" type="email" error={(errors && errors.email)?{content:errors.email[0],pointing:'below'}:null} label={trans('messages.users.email')} onChange={this.handleAddUserFieldChange}/>
                                    <Form.Input required name="phone" error={(errors && errors.phone)?{content:errors.phone[0],pointing:'below'}:null} value={(this.state.newuser && this.state.newuser.phone)?this.state.newuser.phone:''} label={trans('messages.users.phone')} onChange={this.handleAddUserFieldChange}/>
                                    <Form.Input required name="password" error={(errors && errors.password)?{content:errors.password[0],pointing:'below'}:null} type="password" label={trans('messages.users.password')} onChange={this.handleAddUserFieldChange}/>
                                </Form>
                            </Modal.Content>
                            <Modal.Actions>
                                <Button negative onClick={this.closeUserAdd}>
                                    <Icon name='x' /> {trans('messages.users.cancel')}
                                </Button>
                                <Button positive onClick={this.addUser}>
                                    <Icon name='checkmark' />  {trans('messages.users.save')}
                                </Button>
                            </Modal.Actions>
                        </Modal>
                    </Menu.Item>
                </Menu.Menu>
            </Menu>
            <Form>
                <Form.Group>
                    <Form.Input placeholder={trans('messages.search')} width={8} icon="search" onChange={this.handleSearch} name="search" defaultValue={(search.search)?search.search:''}/>
                    <Form.Select name="role" options={USER_ROLES_SEARCH} value={(search.role)?search.role:''} placeholder={trans('messages.users.role')} onChange={this.handleSearch}/>
                </Form.Group>
            </Form>
            <Divider horizontal>{trans('messages.users.list')}</Divider>
                {
                    users.fetching
                    ?<Dimmer active inverted><Loader /></Dimmer>
                    :<Table celled selectable>
                        <Table.Header>
                            <Table.Row>
                                <Table.HeaderCell>#</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.users.name')}</Table.HeaderCell>
                                <Table.HeaderCell>Роль</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.users.email')}</Table.HeaderCell>
                                <Table.HeaderCell>Индивидуальная ставка</Table.HeaderCell>
                                <Table.HeaderCell>&nbsp;</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>
                        <Table.Body>
                            {users.data.map((user,i)=>{return (<User key={i} user={user}/>); })}
                        </Table.Body>
                    </Table>
                }
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            users:state.users
        };
    },
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(Users);
