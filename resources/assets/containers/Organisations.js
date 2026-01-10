import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Table } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Organisation from '../components/Organisation';

class Organisations extends Component {
    constructor(props){
        super(props);
        this.state = {
            createFormOpen:false,
            errors:false,
            search:{},
            newItem:{}
        };
        this.closeCreateForm = this.closeCreateForm.bind(this);
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

        newItem.limit = 49900000;

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
            'organisation',
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
                    'organisation',
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
                    'organisation',
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
        const {expensetypes,organisations} = this.props;
        const {createFormOpen,errors,search} = this.state;
        const expensetypeOptions = (expensetypes.fetching && expensetypes && expensetypes.data)?[]:expensetypes.data.map( (item,i) => {
            return {
                value: item.id,
                text: item.name || ''
            }
        });
        const ndsOptions = [
            {
                value: 0.1,
                text: '10%'
            },
            {
                active: true,
                selected: true,
                value: 0.22,
                text: '22%'
            }
        ];
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.organisations.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                    <Menu.Item>
                        <Button icon primary onClick={()=>{this.setState({createFormOpen:true})}}><Icon name="plus"></Icon></Button>
                        <Modal open={createFormOpen} closeIcon={<Icon name="close" onClick={()=>{this.setState({createFormOpen:false})}}/>}>
                            <Header icon='building' corner="top right" content={trans('messages.add')} />
                            <Modal.Content>
                                <Form>
                                    <Form.Select loading={(expensetypes.fetching && expensetypes.data)} required name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={expensetypeOptions}  label={trans('messages.expensetypes.title')} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Select required name="data.nds" error={(errors && errors.nds)?{content:errors.nds[0],pointing:'below'}:null} options={ndsOptions} label={trans('messages.nds')} onChange={this.handleCreateFormFieldChange} defaultValue={0.22}/>
                                    <Form.Input required name="name" label={trans('messages.name')} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input required name="data.cashier" label="Кассир" onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="data.inn" label={trans('messages.organisations.inn')} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="data.kpp" label={trans('messages.organisations.kpp')} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="data.okveds" label={trans('messages.organisations.okveds')} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input name="data.address" label={trans('messages.organisations.address')} onChange={this.handleCreateFormFieldChange}/>
                                    {/*
                                        <Divider horizontal>{trans('messages.organisations.1c_settings')}</Divider>
                                        <Form.Input name="data.1c.url" label={trans('messages.organisations.1c_host')} onChange={this.handleCreateFormFieldChange}/>
                                        <Form.Input name="data.1c.login" label={trans('messages.organisations.1c_login')} onChange={this.handleCreateFormFieldChange}/>
                                        <Form.Input name="data.1c.password" icon="lock" label={trans('messages.organisations.1c_password')} onChange={this.handleCreateFormFieldChange}/>
                                    */}
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
                organisations.fetching
                ?<Dimmer active inverted><Loader /></Dimmer>
                :<Table celled selectable>
                    {/* <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell></Table.HeaderCell>
                            <Table.HeaderCell></Table.HeaderCell>
                            <Table.HeaderCell></Table.HeaderCell>
                            <Table.HeaderCell></Table.HeaderCell>
                            <Table.HeaderCell></Table.HeaderCell>
                        </Table.Row>
                    </Table.Header> */}
                    <Table.Body>
                        {
                            organisations.data.map((item,i)=>{return (<Organisation key={i} item={item} expensetypes={expensetypes}/>); })
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
            expensetypes:state.expensetypes,
            organisations:state.organisations
        };
    },
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(Organisations);
