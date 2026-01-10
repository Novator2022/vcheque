import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header, Table } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,UPDATE} from '../constants';

class Nomenclature extends Component {
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
    updateNomenclature = () => {
        const {item} = this.state;
        this.props.action(
            'nomenclature',
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
    removeNomenclature = () => {
        const {item} = this.state;
        this.props.action(
            'nomenclature',
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
            'nomenclature',
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
                value: "22",
                text: '22%'
            }
        ];
        return editing
            ?<Item>
                <Item.Content>
                    <Item.Header>#<small>{item.id}</small>{item.name}</Item.Header>
                    <Item.Description>
                        <Form>
                            <Form.Group>
                                <Form.Input name="name" label={trans('messages.nomenclatures.name')} onChange={this.handleFieldChange} defaultValue={item.name}/>
                                <Form.Select name="nds" label={trans('messages.nds')} onChange={this.handleFieldChange} options={ndsOptions} defaultValue={item.nds}/>
                            </Form.Group>
                            <Form.TextArea name="description" label={trans('messages.description')} onChange={this.handleFieldChange} defaultValue={item.description}/>
                        </Form>
                    </Item.Description>
                    <Item.Extra>
                        <Menu secondary stackable borderless>
                            <Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="grey" icon onClick={handleEditing}><Icon name="undo"/>{trans('messages.cancel')}</Button>
                                </Menu.Item>
                                <Menu.Item>
                                    <Button color="green" icon  onClick={this.updateNomenclature}><Icon name="pencil"/>{trans('messages.save')}</Button>
                                </Menu.Item>
                                <Menu.Item>
                                    <Button color="red" icon onClick={this.removeNomenclature}><Icon name="x"/>{trans('messages.remove')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                        </Menu>
                    </Item.Extra>
                </Item.Content>
            </Item>
            :<Table.Row>
                <Table.Cell>{item.id}</Table.Cell>
                <Table.Cell>{item.name}</Table.Cell>
                <Table.Cell>{item.description}</Table.Cell>
                <Table.Cell textAlign="right">{item.nds}</Table.Cell>
                <Table.Cell textAlign="right">
                    { item.deleted_at
                        ?<Button size="mini" color="grey" icon onClick={this.handleRestore}><Icon name="checkmark"/>{trans('messages.activate')}</Button>
                        :<Button.Group size="mini">
                            <Button color="green" icon onClick={handleEditing}><Icon name="pencil"/>{trans('messages.edit')}</Button>
                            <Button color="red" icon onClick={this.removeNomenclature}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                        </Button.Group>
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
)(Nomenclature);
