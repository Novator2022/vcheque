import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Table, Pagination } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Log from '../components/Log';
import {paginate} from '../helpers/paginator';

class Logs extends Component {
    constructor(props){
        super(props);
        this.state = {
            search:{},
            page: 1
        };
    }
    componentDidMount(){
        this.props.action('log', 'LIST');
        this.props.action('user', 'LIST');
        this.props.action('contractor', 'LIST');
        this.props.action('organisation', 'LIST');
    }
    handleSearch = (e,{name,value})=>{
        let {search} = this.state;
        search[name] = value;

        if(e.target.type=="checkbox")search[name] = e.target.checked?"on":"off";
        this.setState({search: search},
            ()=>{
                this.props.action(
                    'log',
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
        const {logs,expensetypes,nomenclatures,organisations,users} = this.props;
        const {search} = this.state;
        const typeOptions = [
            {
                value: "cheque",
                text: trans('messages.cheques.title')
            },
            {
                value: "user",
                text: trans('messages.users.title')
            },
        ];
        const userOptions = [{
            value: "",
            text: trans('messages.all')
        }];
        if(!users.fetching) users.data.map( (item,i) => {
            userOptions.push({
                value: item.id,
                text: item.name
            })
        });

        const pagination = paginate(logs.data, this.state.page);
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.logs.title')}</Header></Menu.Item>
            </Menu>
            <Form>
                <Form.Group>
                    <Form.Input placeholder={trans('messages.search')} width={8} icon="search" onChange={this.handleSearch} name="search" defaultValue={(search.search)?search.search:''}/>
                    <Form.Select multiple placeholder={trans('messages.users.title')} loading={users.fetching} name="user_id" options={userOptions} value={(search.user_id)?search.user_id:[]} onChange={this.handleSearch}/>
                    <Form.Select multiple placeholder={trans('messages.types.title')} name="user_id" name="type" options={typeOptions} value={(search.type)?search.type:[]} onChange={this.handleSearch}/>
                </Form.Group>
            </Form>
            <Divider horizontal>{trans('messages.list')}</Divider>
            {
                logs.fetching
                ?(<Dimmer active inverted><Loader /></Dimmer>)
                : <Item.Group divided>
                    {
                        pagination.data.map( (item,i) => {
                            return (<Log key={i} item={item}  organisations={organisations} nomenclatures={nomenclatures} expensetypes={expensetypes} contractors={this.props.contractors} users={this.props.users}/>);
                        })
                    }
                    <Item>
                        <Item.Content>
                            <div align="center">
                                <Pagination
                                    boundaryRange={3}
                                    activePage={pagination.page}
                                    siblingRange={1}
                                    totalPages={pagination.pages}
                                    onPageChange={(e, data) => {this.setState({page: data.activePage, paginating: true});}}
                                  />
                            </div>
                        </Item.Content>
                    </Item>
                </Item.Group>
                /*:<Table selectable>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell>#</Table.HeaderCell>
                            <Table.HeaderCell><Icon name="calendar"/>{trans('messages.date')}</Table.HeaderCell>
                            <Table.HeaderCell><Icon name="user"/>{trans('messages.users.title')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.description')}</Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {
                            pagination.data.reverse().map( (item,i) => {
                                return (<Log key={i} item={item}  organisations={organisations} nomenclatures={nomenclatures} expensetypes={expensetypes} contractors={this.props.contractors} users={this.props.users}/>);
                            })
                        }
                    </Table.Body>
                    <Table.Footer>
                        <Table.Row>
                            <Table.Cell colSpan="8" align="center">
                                <div align="center">
                                    <Pagination
                                        boundaryRange={3}
                                        activePage={pagination.page}
                                        siblingRange={1}
                                        totalPages={pagination.pages}
                                        onPageChange={(e, data) => {
                                      />
                                </div>
                            </Table.Cell>
                        </Table.Row>
                    </Table.Footer>
                </Table>*/
            }
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            logs:state.logs,
            nomenclatures:state.nomenclatures,
            contractors:state.contractors,
            organisations:state.organisations,
            expensetypes:state.expensetypes,
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
)(Logs);
