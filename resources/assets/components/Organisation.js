import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu, Divider, Dimmer, Loader, Item, Label, Image, Icon, Header, Table, Checkbox } from 'semantic-ui-react';
import numeral from 'numeral';

import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,RESTORE,UPDATE} from '../constants';

class Organisation extends Component {
    constructor(props){
        super(props);
        this.state={
            item: this.props.item,
            errors: false,
            editing:false
        }
    }
    handleFieldChange = (e, {name,value, checked}) => {
        let {item} = this.state;
        const pattern = /\S+\.\S+.*/ig;
        if(checked != undefined) {
            item[name] = checked;
        }
        else if(pattern.test(name)){
            let rr = item;
            name.split(/\./g).map(field=>{
                rr[field] = (rr[field]!=undefined && !Array.isArray(rr[field])) ? rr[field] : {};
                rr = rr[field];
            })
            eval(`item.${name}="${value}"`);
        }
        else item[name] = value;
        this.setState({item:item});
    }
    updateOrganisation = () => {
        const {item} = this.state;
        let newItem = {};
        for(let i in item){
            const val = item[i];
            if(typeof(val)!="object")newItem[i]=val;
        }
        this.props.action(
            'organisation',
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
    restoreOrganisation = () => {
        const {item} = this.state;
        this.props.action(
            'organisation',
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
    handleRestore = () => {
        const {item} = this.state;
        this.props.action(
            'organisation',
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
    removeOrganisation = () => {
        const {item} = this.state;
        this.props.action(
            'organisation',
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
        let nds = item.data.nds?item.data.nds:false;

        const expensetypeOptions = (expensetypes.fetching)?[]:expensetypes.data.map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name
            }
        });
        const ndsOptions = [
            {
                key:0,
                value: 0,
                text: '0%'
            },
            {
                key:1,
                value: 0.1,
                text: '10%'
            },
            {
                key:2,
                value: 0.22,
                text: '22%'
            }
        ];
        const gmtOptions = [
            {
                value: 2,
                text: '+2 GMT'
            },
            {
                value: 3,
                text: '+3 GMT'
            },
            {
                value: 4,
                text: '+4 GMT'
            },
            {
                value: 5,
                text: '+5 GMT'
            },
            {
                value: 6,
                text: '+6 GMT'
            },
            {
                value: 7,
                text: '+7 GMT'
            },
            {
                value: 8,
                text: '+8 GMT'
            },
            {
                value: 9,
                text: '+9 GMT'
            }
        ];
        return (editing
            ?(<Table.Row>
                <Table.Cell colspan={5}>
                    <Item className={editing?"selected-editing":''}>
                        <Item.Content>
                            <Item.Header>#<small>{item.id}</small>{item.name}</Item.Header>
                            <Item.Description>
                                <Form>
                                    <Form.Group>
                                        <Form.Select loading={expensetypes.fetching} name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={expensetypeOptions} value={item.expense_type_id} label={trans('messages.expensetypes.title')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="name" error={(errors && errors.name)?{content:errors.name[0],pointing:'below'}:null} defaultValue={item.name || ''} label={trans('messages.name')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="data.inn" defaultValue={item.data.inn} label={trans('messages.organisations.inn')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="data.kpp" defaultValue={item.data.kpp} label={trans('messages.organisations.kpp')} onChange={this.handleFieldChange}/>
                                        <Form.Input width={8} name="data.okveds" defaultValue={item.data.okveds} label={trans('messages.organisations.okveds')} onChange={this.handleFieldChange}/>

                                    </Form.Group>
                                    <Form.Input name="data.address" defaultValue={item.data.address} label={trans('messages.organisations.address')} onChange={this.handleFieldChange}/>
                                    <Form.Group>
                                        <Form.Select name="data.nds" error={(errors && errors.nds)?{content:errors.nds[0],pointing:'below'}:null} options={ndsOptions} label={trans('messages.nds')} onChange={this.handleFieldChange} value={parseFloat(item.data.nds)}/>
                                        <Form.Input name="limit" error={(errors && errors.limit)?{content:errors.limit[0],pointing:'below'}:null} defaultValue={item.limit} label='Лимит' onChange={this.handleFieldChange} icon="ruble"/>
                                    </Form.Group>

                                    <Form.Group>
                                        <Checkbox label='Без НДС' name="is_nonds" checked={item.is_nonds} onChange={this.handleFieldChange} style={{marginRight: "15px", marginLeft: "0.5em"}}/>
                                        <Checkbox label='СНО "УСН доход - расход"' name="is_usn15" checked={item.is_usn15} onChange={this.handleFieldChange}/>
                                    </Form.Group>

                                    <Form.Group>
                                        <Checkbox label='Автоматически отправлять чеки в магазин' name="auto_export" checked={item.auto_export} onChange={this.handleFieldChange} style={{marginRight: "15px", marginLeft: "0.5em"}}/>
                                    </Form.Group>

                                    <Form.Group>
                                        <Checkbox label='Отключить печать чеков на бумаге' name="no_print" checked={item.no_print} onChange={this.handleFieldChange} style={{marginRight: "15px", marginLeft: "0.5em"}}/>
                                    </Form.Group>

                                    <Divider horizontal>Подключение к кассе</Divider>
                                    <Form.Group>
                                        <Checkbox label='Модуль.Касса' name="modulkassa" checked={item.modulkassa} onChange={this.handleFieldChange} style={{marginLeft: "0.5em"}}/>
                                    </Form.Group>
                                    {/*<Divider horizontal>{trans('messages.organisations.1c_settings')}</Divider>
                                    <Form.Group>
                                        <Form.Input name="data.1c.url" label={trans('messages.organisations.1c_host')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="data.1c.login" label={trans('messages.organisations.1c_login')} onChange={this.handleFieldChange}/>
                                        <Form.Input name="data.1c.password" icon="lock" label={trans('messages.organisations.1c_password')} onChange={this.handleFieldChange}/>
                                    </Form.Group>*/}
                                    <Divider horizontal>Кассир</Divider>

                                    <Divider horizontal>{trans('messages.organisations.1c_settings')}</Divider>
                                    { this.state.item.modulkassa ?
                                        <div>
                                            <Form.Input name="data.cashier" defaultValue={item.data.cashier} label="Кассир" onChange={this.handleFieldChange}/>
                                            <Form.Group>
                                                <Form.Select name="data.gmt" error={(errors && errors.gmt) ? {content:errors.gmt[0], pointing:'below'}:null} options={gmtOptions} value={parseInt(item.data.gmt)} label="Часовой пояс" onChange={this.handleFieldChange}/>
                                            </Form.Group>
                                            <Form.Group>
                                                <Form.Input name="data.modulkassa.uid" label="UID" onChange={this.handleFieldChange} defaultValue={item.data.modulkassa ? item.data.modulkassa.uid : ''}/>
                                                <Form.Input name="data.modulkassa.login" label="Логин" onChange={this.handleFieldChange} defaultValue={item.data.modulkassa ? item.data.modulkassa.login : ''}/>
                                                <Form.Input name="data.modulkassa.password" icon="lock" label="Пароль" onChange={this.handleFieldChange} defaultValue={item.data.modulkassa ? item.data.modulkassa.password : ''}/>
                                            </Form.Group>
                                        </div>
                                        :null
                                    }
                                </Form>
                            </Item.Description>
                            <Item.Extra>
                                <Menu secondary stackable borderless>
                                    <Menu.Item>
                                        <Button color="grey" icon onClick={handleEditing}><Icon name="undo"/>{trans('messages.cancel')}</Button>
                                    </Menu.Item>
                                    { item.deleted_at
                                        ?<Menu.Menu position="right">
                                            <Menu.Item>
                                                <Button color="grey" icon onClick={this.handleRestore}><Icon name="checkmark"/>{trans('messages.activate')}</Button>
                                            </Menu.Item>
                                        </Menu.Menu>
                                        :<Menu.Menu position="right">
                                            <Menu.Item>
                                                <Button color="green" icon  onClick={this.updateOrganisation}><Icon name="pencil"/>{trans('messages.save')}</Button>
                                            </Menu.Item>
                                            <Menu.Item>
                                                <Button color="red" icon onClick={this.removeOrganisation}><Icon name="x"/>{trans('messages.deactivate')}</Button>
                                            </Menu.Item>
                                        </Menu.Menu>
                                    }
                                </Menu>
                            </Item.Extra>
                        </Item.Content>
                    </Item>
                </Table.Cell>
            </Table.Row>)
            :(<Table.Row>
                <Table.Cell>{item.id}</Table.Cell>
                <Table.Cell>
                    <Item>
                        <Item.Header>{item.name}</Item.Header>
                        <Item.Meta>ИНН: {item.data.inn || ''}</Item.Meta>
                        <Item.Meta>КПП: {item.data.kpp || ''}</Item.Meta>
                    </Item>
                </Table.Cell>
                <Table.Cell>{item.expense_type?<Label>{item.expense_type.data} <Label.Detail>{item.expense_type.name || ''}</Label.Detail> </Label>:null}</Table.Cell>
                <Table.Cell>{item.data.address || ''}</Table.Cell>
                <Table.Cell>{item.auto_export ? 'Для маркета' : ''}</Table.Cell>
                <Table.Cell style={{ textAlign: "center" }}>
                    Лимит:
                    &nbsp;
                    {numeral(item.limit).format('0,0.00') || '-'}<Icon name="ruble"/>
                </Table.Cell>
                <Table.Cell style={{ textAlign: "center" }}>
                    Выбрано:
                    &nbsp;
                    {numeral(item.reach).format('0,0.00') || '-'}<Icon name="ruble"/>
                </Table.Cell>
                <Table.Cell style={{width: '1px'}}>
                    <Menu secondary stackable borderless>
                        { item.deleted_at
                            ?<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button color="grey" icon onClick={this.restoreOrganisation}><Icon name="checkmark"/>{trans('messages.activate')}</Button>
                                </Menu.Item>
                            </Menu.Menu>
                            :<Menu.Menu position="right">
                                <Menu.Item>
                                    <Button.Group size="small">
                                        <Button color="green" icon onClick={handleEditing}><Icon name="pencil"/></Button>
                                        <Button color="red" icon onClick={this.removeOrganisation}><Icon name="x"/></Button>
                                    </Button.Group>
                                </Menu.Item>
                            </Menu.Menu>
                        }
                    </Menu>
                </Table.Cell>

            </Table.Row>)
            );
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
)(Organisation);
