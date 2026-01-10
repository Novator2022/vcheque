import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Table, Pagination } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Category from '../components/Category';

import {paginate} from '../helpers/paginator.js';

class Categories extends Component {
    constructor(props){
        super(props);
        this.state = {
            createFormOpen:false,
            errors:false,
            search:{},
            newItem:{},
            page: 1
        };
        this.closeCreateForm = this.closeCreateForm.bind(this);
    }
    componentDidMount(){
        this.props.action('category', 'LIST');
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
            'category',
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
                    'category',
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
                    'category',
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
        const {categories} = this.props;
        const {createFormOpen,errors,search} = this.state;
        const ndsOptions = [
            {
                value: "0",
                text: '0%'
            },
            {
                value: "10",
                text: '10%'
            },
            {
                value: "20",
                text: '20%'
            }
        ];

        const pagination = paginate(categories.data, this.state.page);
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.categories.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                    <Menu.Item>
                        <Button icon primary onClick={()=>{this.setState({createFormOpen:true})}}><Icon name="plus"></Icon></Button>
                        <Modal open={createFormOpen}>
                            <Header icon='cart plus' content={trans('messages.add')} />
                            <Modal.Content>
                                <Form>
                                    <Form.Input required name="name" label={trans('messages.categories.name')} onChange={this.handleCreateFormFieldChange}/>
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
                    categories.fetching
                    ?(<Dimmer active inverted><Loader /></Dimmer>)
                    :<Table celled selectable>
                        <Table.Header>
                            <Table.Row>
                                <Table.HeaderCell>#</Table.HeaderCell>
                                <Table.HeaderCell>{trans('messages.categories.name')}</Table.HeaderCell>
                                <Table.HeaderCell>&nbsp;</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>
                        <Table.Body>
                            {
                                pagination.data.map( (item,i) => { return (<Category key={item.id} item={item}/>); })
                            }
                        </Table.Body>
                        <Table.Footer>
                            <Table.Row>
                                <Table.Cell colSpan="5" align="center">
                                    <div align="center">
                                        <Pagination
                                            boundaryRange={3}
                                            activePage={pagination.page}
                                            siblingRange={1}
                                            totalPages={pagination.pages}
                                            onPageChange={(e, data) => {this.setState({page: data.activePage, paginating: true});}}
                                          />
                                    </div>
                                </Table.Cell>
                            </Table.Row>
                        </Table.Footer>
                    </Table>
                }
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            categories:state.categories,
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
)(Categories);
