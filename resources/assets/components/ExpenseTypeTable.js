import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header, Table } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,UPDATE} from '../constants';

class ExpenseTypeTable extends Component {
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
    updateExpenseType = () => {
        const {item} = this.state;
        this.props.action(
            'expensetype',
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
    removeExpenseType = () => {
        const {item} = this.state;
        this.props.action(
            'expensetype',
            REMOVE,
            item,
            (response)=>{
            },
            (error)=>{
            }
        );
    }
    handleRestore = () => {
        const {item} = this.state;
        this.props.action(
            'expensetype',
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
    render() {
        const {item} = this.state;
        const {editing,errors} = this.state;
        const handleEditing = (e)=>{
            this.setState({editing:!this.state.editing});
        }
        return editing
        ?(<Table.Row>
            <Table.Cell colSpan={3}>
                <Item>
                    <Item.Content>
                        <Item.Header><code className="item-id"><small>#{item.id}</small></code>{item.name}</Item.Header>
                        <Item.Description>
                            <Form>
                                <Form.Group>
                                    <Form.Input name="name" error={(errors && errors.name)?{content:errors.name[0],pointing:'below'}:null} defaultValue={item.name} label={trans('messages.name')} onChange={this.handleFieldChange}/>
                                    <Form.Input name="data" error={(errors && errors.data)?{content:errors.data[0],pointing:'below'}:null} defaultValue={item.data} label={trans('messages.expensetypes.data')} onChange={this.handleFieldChange}/>
                                </Form.Group>
                            </Form>
                        </Item.Description>
                        <Item.Extra>
                            <Menu secondary stackable borderless>
                                <Menu.Menu position="right">
                                    <Menu.Item>
                                        <Button color="green" icon  onClick={this.updateExpenseType}><Icon name="pencil"/>{trans('messages.save')}</Button>
                                    </Menu.Item>
                                    <Menu.Item>
                                        <Button color="grey" icon onClick={ () => {this.setState({editing:false})}}><Icon name="x"/>{trans('messages.cancel')}</Button>
                                    </Menu.Item>
                                </Menu.Menu>
                            </Menu>
                        </Item.Extra>
                    </Item.Content>
                </Item>
            </Table.Cell>
        </Table.Row>)
        :(<Table.Row>
            <Table.Cell>{item.data}</Table.Cell>
            <Table.Cell>{item.name}</Table.Cell>
            <Table.Cell>
                <Menu secondary stackable borderless>
                    { item.deleted_at
                        ?<Menu.Menu position="right">
                            <Menu.Item>
                                <Button color="grey" icon onClick={this.handleRestore}><Icon name="checkmark"/>{trans('messages.activate')}</Button>
                            </Menu.Item>
                        </Menu.Menu>
                        :<Menu.Menu position="right">
                            <Menu.Item>
                                <Button color="green" icon onClick={handleEditing}><Icon name="pencil"/>{trans('messages.edit')}</Button>
                            </Menu.Item>
                            <Menu.Item>
                                <Button color="red" icon onClick={this.removeExpenseType}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                            </Menu.Item>
                        </Menu.Menu>
                    }
                </Menu>
            </Table.Cell>
        </Table.Row>)
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
)(ExpenseTypeTable);
