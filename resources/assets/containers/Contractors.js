import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Table } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Contractor from '../components/Contractor';

class Contractors extends Component {
    constructor(props){
        super(props);
        this.state = {
            createFormOpen:false,
            errors:false,
            search:{},
            newItem:{
                user_id:window.auth.can.admin?null:window.auth.id
            }
        };
        this.closeCreateForm = this.closeCreateForm.bind(this);
    }
    componentDidMount(){
        this.props.action('contractor', 'LIST');
        this.props.action('user', 'LIST');
    }
    handleCreateFormFieldChange = (e, {name,value}) => {
        let {newItem} = this.state;
        const pattern = /\S+\.\S+.*/ig;

        if(pattern.test(name)){

            let rr = newItem;
            name.split(/\./g).map(field=>{
                rr[field] = rr[field]!=undefined ? rr[field] : {};
                rr = rr[field];
            })
            eval(`newItem.${name}="${value}"`);
        }
        else newItem[name]=value

        this.setState({newItem:newItem});
    }
    closeCreateForm = () => {
        this.setState({
            createFormOpen:false,
            errors:false,
            newItem:{}
        });
    }
    addItem = (e,data) => {
        const {newItem} = this.state;
        const {closeCreateForm} = this;
        this.props.action(
            'contractor',
            CREATE,
            newItem,
            (response)=>{
                closeCreateForm()
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
        if(e.target.type=="checkbox")search[name] = e.target.checked?"on":"off";
        this.setState({search: search},
            ()=>{
                this.props.action(
                    'contractor',
                    LIST,
                    search,
                    (response)=>{},
                    (error)=>{}
                )
            }
        );
        ;
    }
    handleCheckboxSearch = (e,{name,checked})=>{
        let {search} = this.state;
        search[name] = checked?"on":"off";
        this.setState({search: search},
            ()=>{
                this.props.action(
                    'contractor',
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
        const {auth} = window;
        const {users,contractors} = this.props;
        const {createFormOpen,errors,search} = this.state;
        const userOptions = (users.fetching && users && users.data)?[]:users.data.map( (item,i) => {
            return {
                value: item.id,
                description: item.role,
                text: item.name || ''
            }
        });
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.contractors.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                    <Menu.Item>
                        <Button icon primary onClick={()=>{this.setState({createFormOpen:true})}}><Icon name="plus"></Icon></Button>
                        <Modal open={createFormOpen} closeIcon={<Icon name="close" onClick={()=>{this.setState({createFormOpen:false})}}/>}>
                            <Header icon='building' corner="top right" content={trans('messages.add')} />
                            <Modal.Content>
                                <Form>
                                    {auth.can.admin
                                        ?<Form.Select loading={(users.fetching && users.data)} required name="user_id" error={(errors && errors.user_id)?{content:errors.user_id[0],pointing:'below'}:null} options={userOptions}  label={trans('messages.contractors.user')} onChange={this.handleCreateFormFieldChange}/>
                                        :null
                                    }
                                    <Form.Input required name="name" label={trans('messages.contractors.name')} error={(errors && errors.name)?{content:errors.name[0],pointing:'below'}:null} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="inn" label={trans('messages.contractors.inn')} error={(errors && errors.inn)?{content:errors.inn[0],pointing:'below'}:null}  onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="kpp" label={trans('messages.contractors.kpp')} error={(errors && errors.kpp)?{content:errors.kpp[0],pointing:'below'}:null} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="address" label={trans('messages.contractors.address')} error={(errors && errors.address)?{content:errors.address[0],pointing:'below'}:null} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="percent" label={trans('messages.contractors.percent')} error={(errors && errors.percent)?{content:errors.percent[0],pointing:'below'}:null} onChange={this.handleCreateFormFieldChange}/>
                                </Form>
                            </Modal.Content>
                            <Modal.Actions>
                                <Button negative onClick={this.closeCreateForm}>
                                    <Icon name='x' /> {trans('messages.cancel')}
                                </Button>
                                <Button positive onClick={this.addItem}>
                                    <Icon name='checkmark' />  {trans('messages.save')}
                                </Button>
                            </Modal.Actions>
                        </Modal>
                    </Menu.Item>
                </Menu.Menu>
            </Menu>
            <Form>
                <Form.Group>
                    <Form.Input placeholder={trans('messages.search')} width={8} icon="search" onChange={this.handleSearch} name="search" defaultValue={(search.search)?search.search:''}/>
                    <Form.Checkbox width={8} onChange={this.handleCheckboxSearch} name="deactivated" defaultValue={(search.deactivated)?search.deactivated:''} label={trans('messages.deactivated')}/>
                </Form.Group>
            </Form>
            <Divider horizontal>{trans('messages.list')}</Divider>
            {
                contractors.fetching
                    ?(<Dimmer active inverted><Loader /></Dimmer>)
                    :<Table celled selectable>
                        <Table.Header>
                            <Table.Row>
                                <Table.HeaderCell>#</Table.HeaderCell>
                                <Table.HeaderCell><Icon name="calendar"/>{trans('messages.date')}</Table.HeaderCell>
                                <Table.HeaderCell><Icon name="user"/>{trans('messages.contractors.user')}</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.contractors.name')}</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.contractors.inn')}</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.contractors.kpp')}</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.contractors.address')}</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.contractors.percent')}</Table.HeaderCell>
                                <Table.HeaderCell>&nbsp;</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>
                        <Table.Body>
                            {
                                contractors.data.map( (item,i) => {
                                    return (<Contractor key={i} item={item} users={users}/>);
                                })
                            }
                        </Table.Body>
                    </Table>
            }
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            users:state.users,
            contractors:state.contractors
        };
    },
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(Contractors);
