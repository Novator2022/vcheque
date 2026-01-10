import React, { Component } from 'react'
import {connect} from 'react-redux';
import {Container, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header, Table, Label, Modal, Dropdown, Search, Message } from 'semantic-ui-react';
import {DateInput, TimeInput, DateTimeInput, DatesRangeInput} from 'semantic-ui-calendar-react';

import SemanticDatepicker from 'react-semantic-ui-datepickers';
import ptLocale from 'react-semantic-ui-datepickers/dist/locales/ru-RU';

import QRCode from 'react-qr-code';

import {Amount, Date} from '../simple';
import {modelRest} from '../../actions';
import {trans,USER_ROLES,REMOVE,UPDATE} from '../../constants';

import {INITIAL_POSITION} from '../../containers/ChequeSchedules';
import {VDate} from '../../helpers/dates';

class Edit extends Component {
    constructor(props){
        super(props);

        let item = this.props.item;
        if (item.data.positions == undefined && Array.isArray(item.data)) {
            let poss = [];
            let contractor = null;
            item.data.map((i) => {
                if(i.positions) {
                    i.positions.map((p) => {
                        poss.push(p);
                    });
                }
                if(i.contractor) {
                    contractor = i.contractor;
                }
            });
            item.data = {
                positions: poss,
                contractor: contractor
            };
        }

        this.state={
            item: item,
            errors: false,
            nomenclatureSearch:false,
            newItem:{},
            position:INITIAL_POSITION,
        }
    }

    handleFieldChange = (e, {name,value}) => {
        let {item} = this.state;
        item[name] = value;
        this.setState({item: item});
    }
    handlePositionFieldChange = (e, {name,value}) => {
        let {item} = this.state;
        item[name] = value;
        this.setState({item: item});
    }
    handleDatesChange = (dates) =>{
        let {item} = this.state;
        let dd = null;
        if(dates && dates.length>1){
            dd = dates.map( d => {
                const date = d;
                return `${date.getFullYear()}-${date.getMonth()+1}-${date.getDate()+1}`;
            });
            item.period_start = VDate.truncDate(dates[0]);
            item.period_end = VDate.truncDate(dates[1], true);
        }
    }
    handlePositionFieldChange = (e, {name,value}) => {
        let {position} = this.state;
        const {nomenclatures} = this.props;
        position[name] = value;
        if(name == 'nomenclature_id') {
            const nomenclature = nomenclatures.data.find( item => {return (item.id == value)} );
            position.name = nomenclature.name;
            position.nds = nomenclature.nds;
        }
        if (name == "amount_from") {
            position[name] = value.replace(/,/ig, ".");
            position["amount_to"] = position[name];
        } else if (name == "quantity_from") {
            position[name] = value.replace(/,/ig, ".");
            position["quantity_to"] = position[name];
        }
        this.setState({position:position});
    }
    handleAddPosition = (e) => {
        const {nomenclatures} = this.props;
        let {item ,position} = this.state;


        if ( !position.nomenclature || !position.amount_from || !position.amount_to || !position.quantity_from || !position.quantity_to) return;
        item.data = item.data ? item.data : {};
        item.data.positions = item.data.positions ? item.data.positions : [];
        const newPosition = {...position};
        item.data.positions.push(newPosition);

        this.setState({item:item, position: INITIAL_POSITION});
    }
    handleSearchNomenclature = (e, data) => {

        this.handlePositionFieldChange(e,{name:'nomenclature',value:data.value});
        this.setState({nomenclatureSearch:data.value});
    }
    handleSearchOnResultNomenclature = (e,{result}) => {
        this.handlePositionFieldChange(e,{name:"nomenclature",value:result.title});
        this.handlePositionFieldChange(e,{name:"nomenclature_id",value:result.value});
        this.handlePositionFieldChange(e,{name:"nds",value:result.nds/100});
    }
    handleUpdate = () => {
        this.props.handleUpdate(this.state.item);
        this.props.handleClose();
    }
    render() {
        const {item, errors, position} = this.state;
        const {contractors} = this.props;
        const {organisation, user_id} = item;
        const contractor = (item.data && item.data.contractor) ? item.data.contractor : null;
        let {totalAmount,totalNds,totalQuantity,error} = {totalAmount:0, totalNds:0, totalQuantity:0, error: false};

        const nds = (item.organisation && item.organisation.data.nds) ? item.organisation.data.nds : 0;
        const nomenclatureResults = this.props.nomenclatures.data.filter( (item,i) => { const pattern = new RegExp(`.*${(this.state.nomenclatureSearch!==false)?this.state.nomenclatureSearch:''}.*`,'ig'); return pattern.test(item.name);}).map( (item,i) => {
            return{
                key: i,
                value: item.id,
                title: item.name,
                nds: item.nds,
                description: `${trans('messages.nds')}: ${item.nds}%`
            };
        })

        const contractorOptions = (this.props.contractors.fetching) ? [] : this.props.contractors.data.filter( item=>{ if(user_id==undefined || user_id == null)return true; return (item.user_id == user_id)  } ).map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.email
            }
        })
        const itemPeriod = [
            VDate.fromEnDate(item.period_start),
            VDate.fromEnDate(item.period_end)
        ];

        return <Modal closeIcon={<Icon name="close" onClick={this.props.handleClose}/>} open={this.props.show} centered={false} closeOnDimmerClick={false} closeOnDocumentClick={false}>
            <Header icon='pencil' content="Редактирование" />
            <Modal.Content>
                <Form>
                    <Form.Group>
                        <SemanticDatepicker
                                    format='DD.MM.YYYY'
                                    width={8}
                                    locale={ptLocale}
                                    onDateChange={this.handleDatesChange}
                                    selected={itemPeriod}
                                    required
                                    label="Период действия"
                                    type="range"
                                    width={8}/>
                        <Form.Select
                            required
                            name="periodic" error={(errors && errors.periodic)?{content:errors.periodic[0],pointing:'below'}:null}
                            options={window.cheques.periodicOptions}
                            value={item.periodic}
                            label="Периодичность"
                            onChange={this.handleFieldChange}/>
                    </Form.Group>
                </Form>
                <Form>
                    <Form.Group>
                        <Form.Select width={8} search required name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={this.props.expensetypeOptions} value={item.expense_type_id} label={trans('messages.expensetypes.title')} onChange={this.handleFieldChange}/>
                        <Form.Select width={8} search key={'cheque_organisation_id'} required name="organisation_id" error={(errors && errors.organisation_id)?{content:errors.organisation_id[0],pointing:'below'}:null} options={this.props.organisationOptions} value={item.organisation_id}  label={trans('messages.organisations.title')} onChange={this.handleFieldChange}/>
                    </Form.Group>
                </Form>
                <Divider/>
                <Container className="cheque">
                    <div align="center">
                        <Table collapsing compact size="small" align="center">
                            <Table.Body>
                                { (item.data && item.data.positions) ?
                                    item.data.positions.map( (position,i) => {
                                        const nds = parseFloat(position.nds || ((organisation !== null) ? organisation.data.nds : 0));
                                        totalAmount += position.amount_from * position.quantity_from;
                                        totalNds += calculateNds (position.quantity_from * position.amount_from, 100*nds);
                                        totalQuantity += position.quantity_from;
                                        error = (organisation!=undefined && (organisation.limit < totalAmount)) ;
                                        return (<Table.Row key={i}>
                                            <Table.Cell width={12}>{position.nomenclature}({trans('messages.nds')} {nds*100}%)</Table.Cell>
                                            <Table.Cell width={4} textAlign="right">
                                                {position.quantity_from}x{position.amount_from}<Icon name="ruble"/><br/>=<strong>{position.amount_from * position.quantity_from}<Icon name="ruble"/></strong>
                                                <a href="#" onClick={()=>this.handleRemovePosition(i)}><Icon name="x"/></a>
                                            </Table.Cell>
                                        </Table.Row>);
                                    }) : null}
                            </Table.Body>
                            {(totalQuantity>0)?<Table.Footer>
                                <Table.Row>
                                    <Table.Cell width={8} textAlign="right">{trans('messages.totals.amount')}</Table.Cell>
                                    <Table.HeaderCell width={8} textAlign="right"><Amount value={totalAmount}/><Icon name="ruble"/></Table.HeaderCell>
                                </Table.Row>
                                <Table.Row>
                                    <Table.Cell width={8} textAlign="right">{trans('messages.totals.nds')}</Table.Cell>
                                    <Table.HeaderCell width={8} textAlign="right"><Amount value={totalNds}/><Icon name="ruble"/></Table.HeaderCell>
                                </Table.Row>
                            </Table.Footer>:null}
                        </Table>
                    </div>
                    <Form>
                        <Form.Group>
                            <Form.Field>
                                <label>{trans('messages.nomenclatures.title')}</label>
                                <Search
                                    width={8} required name="nomenclature"
                                    onResultSelect={this.handleSearchOnResultNomenclature}
                                    onSearchChange={this.handleSearchNomenclature}
                                    results={nomenclatureResults}
                                    />
                            </Form.Field>
                            <Form.Input width={4} required type="number" step={1} name="amount_from" value={position.amount_from || ''} label={trans('messages.costs_from')} icon="ruble" onChange={this.handlePositionFieldChange}/>
                            <Form.Input
                                width={3} required type="number" step={1} name="quantity_from" value={position.quantity_from} label={trans('messages.quantity_from')}
                                onChange={this.handlePositionFieldChange}
                                onBlur={this.handleAddPosition}
                                />
                            <Form.Button width={1} disabled={error} type="button" step={1} label="&nbsp;" onClick={this.handleAddPosition} color="orange" icon><Icon name="plus"/></Form.Button>
                        </Form.Group>
                    </Form>
                </Container>
                <Divider/>
                <Form>
                    <Form.Group>
                        {
                            auth.can.admin ? <Form.Select name="user_id" onChange={this.handleCreateFormFieldChange} options={this.props.userOptions} value={item.user_id} label={trans('messages.users.title')}/> : null
                        }
                        <Form.Select name="contractor_id" onChange={this.handleFieldChange} options={contractorOptions} value={item.data.constructor_id || null} label={trans('messages.contractors.title')}/>
                    </Form.Group>
                </Form>
                {/*<Divider/>
                <Form>
                    <Form.Checkbox name="upd" onChange={this.handleFieldChange} label={trans('messages.cheque_schedule.upd')}/>
                    <Form.Checkbox name="sf" onChange={this.handleFieldChange} label={trans('messages.cheque_schedule.sf')}/>
                </Form>*/}

                {
                    error
                    ?<Message negative>
                        <Icon name="warning sign"/> {trans('messages.organisations.limit_reached')}
                    </Message>
                    :null
                }
            </Modal.Content>
            <Modal.Actions>
                <Button negative onClick={this.props.handleClose}><Icon name='x' /> {trans('messages.cancel')}</Button>
                <Button positive onClick={this.handleUpdate} ><Icon name='checkmark' />  {trans('messages.save')}</Button>

            </Modal.Actions>
        </Modal>;
    }
}

class ChequeSchedule extends Component {
    constructor(props){
        super(props);
        this.state={
            item: this.props.item,
            errors: false,
            viewing:false
        }
        this.handleOpen = this.handleOpen.bind(this);
    }
    handleFieldChange = (e, {name,value}) => {
        let {item} = this.state;
        item[name] = value;
        this.setState({item:item});
    }
    handleOpen = () => {
        this.setState({viewing: true});
    }
    handleClose = () => {
        this.setState({viewing:false});
    }
    handleUpdate = (item) => {
        this.props.action(
            'cheque_schedule',
            UPDATE,
            item,
            (response)=>{},
            (error)=>{}
        );
    }
    handleRemove = () => {
        const {item} = this.state;
        this.props.action(
            'cheque_schedule',
            REMOVE,
            item,
            (response)=>{},
            (error)=>{}
        );
    }
    render() {
        const {organisations,expensetypes, noborder} = this.props;
        const {item,viewing} = this.state;
        const {editing,errors} = this.state;
        const {organisation, user_id} = item;
        const {contractor} = item.data;
        const handleEditing = (e)=>{
            this.setState({editing:!this.state.editing});
        }
        const nds = (organisation && organisation.data.nds) ? organisation.data.nds : 0;
        const organisationOptions = organisations.data.filter( (item,i) => { return (item.expense_type_id == item.expense_type_id) }).map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.limit
            }
        })
        const expensetypeOptions = (expensetypes.fetching)?[]:expensetypes.data.map( (item,i) => {
            return {
                key: item.id,
                value: item.id,
                text: item.name
            }
        });

        let completed = item.completed_at ? <div><small>{item.completed_at}</small></div> : '';

        return <Table.Row>
                    <Table.Cell>
                        <Dropdown text={item.id.toString()} closeOnChange={true} onClick={(e,data) => {}}>
                            <Dropdown.Menu>
                                <Dropdown.Item onClick={this.handleOpen}><Icon name="pencil"/> Редактировать</Dropdown.Item>
                                <Dropdown.Item onClick={this.handleRemove}><Icon name="trash"/>Удалить</Dropdown.Item>
                            </Dropdown.Menu>
                        </Dropdown>
                        <Edit
                            show={this.state.viewing}
                            item={item}
                            handleUpdate={this.handleUpdate}
                            handleClose={this.handleClose}
                            expensetypeOptions={expensetypeOptions}
                            organisationOptions={organisationOptions}
                            nomenclatures={this.props.nomenclatures}
                            contractors={this.props.contractors}
                            userOptions={this.props.userOptions}
                            />
                    </Table.Cell>
                    <Table.Cell><Date value={item.created_at}/></Table.Cell>
                    <Table.Cell>{item.type=='schedule' && item.user ? item.user.name : 'Розничный чек'}</Table.Cell>
                    <Table.Cell>{item.organisation ? item.organisation.name :''}</Table.Cell>
                    <Table.Cell>{item.expense_type ? item.expense_type.name :''}</Table.Cell>
                    <Table.Cell><Date value={item.period_start}/></Table.Cell>
                    <Table.Cell><Date value={item.period_end}/></Table.Cell>
                    <Table.Cell>{window.cheques.findByValue(window.cheques.periodicOptions, item.periodic).text} {completed}</Table.Cell>
                    <Table.Cell><a href={'/download/schedules/' + item.id}>скачать</a></Table.Cell>
                </Table.Row>
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
)(ChequeSchedule);
