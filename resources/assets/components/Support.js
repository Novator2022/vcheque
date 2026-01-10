import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,RESTORE,UPDATE} from '../constants';

class Support extends Component {
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
    updateSupport = () => {
        const {item} = this.state;
        let newItem = {};
        for(let i in item){
            const val = item[i];
            if(typeof(val)!="object")newItem[i]=val;
        }
        this.props.action(
            'support',
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
    restoreSupport = () => {
        const {item} = this.state;
        this.props.action(
            'support',
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
    removeSupport = () => {
        const {item} = this.state;
        this.props.action(
            'support',
            REMOVE,
            item,
            (response)=>{
            },
            (error)=>{
            }
        );
    }
    render() {
        const {expensetypes} = this.props;
        const {item} = this.state;
        const {editing,errors} = this.state;
        const handleEditing = (e)=>{
            this.setState({editing:!this.state.editing});
        }
        const expensetypeOptions = (expensetypes.fetching)?[]:expensetypes.data.map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name
            }
        });
        return (<Item>
            {editing
            ?<Item.Content>
                <Item.Header>#<small>{item.id}</small>{item.name}</Item.Header>
                <Item.Description>
                    <Form>
                        <Form.Group>
                            <Form.Select loading={expensetypes.fetching} name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={expensetypeOptions} value={item.expense_type_id} label={trans('messages.expensetypes.title')} onChange={this.handleFieldChange}/>
                            <Form.Input name="name" error={(errors && errors.name)?{content:errors.name[0],pointing:'below'}:null} defaultValue={item.name} label={trans('messages.name')} onChange={this.handleFieldChange}/>
                            <Form.Input name="data.inn" defaultValue={item.data.inn} label={trans('messages.supports.inn')} onChange={this.handleFieldChange}/>
                            <Form.Input name="data.kpp" defaultValue={item.data.kpp} label={trans('messages.supports.kpp')} onChange={this.handleFieldChange}/>
                            <Form.Input name="data.address" defaultValue={item.data.address} label={trans('messages.supports.address')} onChange={this.handleFieldChange}/>
                        </Form.Group>
                        <Form.Group>
                            <Form.Input name="limit" error={(errors && errors.limit)?{content:errors.limit[0],pointing:'below'}:null} defaultValue={item.limit} label={trans('messages.limit')} onChange={this.handleFieldChange} icon="ruble"/>
                        </Form.Group>
                    </Form>
                </Item.Description>
                <Item.Extra>
                    <Menu secondary stackable borderless>
                        <Menu.Menu position="right">
                            <Menu.Item>
                                <Button color="green" icon  onClick={this.updateSupport}><Icon name="pencil"/>{trans('messages.save')}</Button>
                            </Menu.Item>
                            <Menu.Item>
                                <Button color="red" icon onClick={this.removeSupport}><Icon name="x"/>{trans('messages.remove')}</Button>
                            </Menu.Item>
                        </Menu.Menu>
                    </Menu>
                </Item.Extra>
            </Item.Content>
            :(<Item.Content>
                <Item.Header>#<small>{item.id}</small> <Icon name="calendar"/>{item.created_at}</Item.Header>
                <Item.Meta><Icon name="user"/>{item.user.name}</Item.Meta>
                <Item.Description>
                    {item.message}
                </Item.Description>
                <Item.Extra>
                    <Menu secondary stackable borderless>
                        { item.deleted_at
                            ?<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="grey" icon onClick={this.restoreSupport}><Icon name="checkmark"/>{trans('messages.activate')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                            :<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="green" icon onClick={handleEditing}><Icon name="pencil"/>{trans('messages.edit')}</Button>
                                </Menu.Item>
                                <Menu.Item>
                                    <Button color="red" icon onClick={this.removeSupport}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                        }
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
)(Support);
