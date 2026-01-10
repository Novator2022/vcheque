import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,UPDATE} from '../constants';

class ExpenseType extends Component {
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
    render() {
        const {item} = this.state;
        const {editing,errors} = this.state;
        const handleEditing = (e)=>{
            this.setState({editing:!this.state.editing});
        }
        return (<Item>
            {editing
            ?<Item.Content>
                <Item.Header><code className="item-id"><small>#{item.id}</small></code>{item.name}</Item.Header>
                <Item.Description>
                    <Form>
                        <Form.Group>
                            <Form.Input name="name" error={(errors && errors.name)?{content:errors.name[0],pointing:'below'}:null} defaultValue={item.name} label={trans('messages.name')} onChange={this.handleFieldChange}/>
                            <Form.Input name="data" error={(errors && errors.data)?{content:errors.data[0],pointing:'below'}:null} defaultValue={item.data} label={trans('messages.data')} onChange={this.handleFieldChange}/>
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
                                <Button color="red" icon onClick={this.removeExpenseType}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                            </Menu.Item>
                        </Menu.Menu>
                    </Menu>
                </Item.Extra>
            </Item.Content>
            :(<Item.Content>
                <Item.Header>#<small>{item.id}</small>{item.name}</Item.Header>
                <Item.Meta>{item.data}</Item.Meta>
                <Item.Description></Item.Description>
                <Item.Extra></Item.Extra>
                <Item.Extra>
                    <Menu secondary stackable borderless>
                        <Menu.Menu position="right">
                            <Menu.Item>
                                <Button color="green" icon onClick={handleEditing}><Icon name="pencil"/>{trans('messages.edit')}</Button>
                            </Menu.Item>
                            <Menu.Item>
                                <Button color="red" icon onClick={this.removeExpenseType}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                            </Menu.Item>
                        </Menu.Menu>
                    </Menu>
                </Item.Extra>
            </Item.Content>)}
        </Item>);

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
)(ExpenseType);
