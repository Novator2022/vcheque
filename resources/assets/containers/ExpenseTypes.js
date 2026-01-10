import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Table } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import ExpenseType from '../components/ExpenseType';
import ExpenseTypeTable from '../components/ExpenseTypeTable';

class ExpenseTypes extends Component {
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
        newItem[name] = value;
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
            'expensetype',
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
        this.setState({search: search},
            ()=>{
                this.props.action(
                    'expensetype',
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
                    'expensetype',
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
        const {expensetypes} = this.props;
        const {createFormOpen,errors,search} = this.state;
        return <Segment>

            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.expensetypes.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                    <Menu.Item>
                        <Button icon primary onClick={()=>{this.setState({createFormOpen:true})}}><Icon name="plus"></Icon></Button>
                        <Modal closeIcon={<Icon name="close" onClick={()=>{this.setState({createFormOpen:false})}}/>} open={createFormOpen}>
                            <Header icon='user plus' content={trans('messages.add')} />
                            <Modal.Content>
                                <Form>
                                    <Form.Input required name="name"  label={trans('messages.expensetypes.name')} onChange={this.handleCreateFormFieldChange}/>
                                    <Form.Input required name="data"  label={trans('messages.expensetypes.data')} onChange={this.handleCreateFormFieldChange}/>
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
            {expensetypes.fetching
                ?(<Dimmer active inverted><Loader /></Dimmer>)
                :<Table size="small">
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell width={4}>{trans('messages.expensetypes.data')}</Table.HeaderCell>
                            <Table.HeaderCell width={8}>{trans('messages.expensetypes.name')}</Table.HeaderCell>
                            <Table.HeaderCell width={4}>{trans('messages.actions')}</Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {
                            (expensetypes.data.map((item,i)=>{return (<ExpenseTypeTable key={i} item={item}/>); }))
                        }
                    </Table.Body>
                </Table>
            }
            <Item.Group divided></Item.Group>
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            expensetypes:state.expensetypes
        };
    },
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(ExpenseTypes);
