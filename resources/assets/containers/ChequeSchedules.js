import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Button, Container, Divider,Dimmer, Dropdown, Item, Image, Icon, Grid, Form, Header, Label, Loader, Menu, Message, MessageHeader, Modal, Segment, Search,  Statistic, Table, Popup } from 'semantic-ui-react';
import SemanticDatepicker from 'react-semantic-ui-datepickers';
import ptLocale from 'react-semantic-ui-datepickers/dist/locales/ru-RU';
import 'react-semantic-ui-datepickers/dist/react-semantic-ui-datepickers.css';
import numeral from 'numeral';
import {VDate} from '../helpers/dates';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import ChequeSchedule from '../components/ChequeSchedule/ChequeSchedule';
import {Amount, FileUpload} from '../components/simple';

export const INITIAL_POSITION = {
    nomenclature_id:'',
    nomenclature:'',
    name: '',
    amount_from:1,
    amount_to:10,
    quantity_from:1,
    quantity_to:10,
    nds:0.2,
};

class ChequeSchedules extends Component {
    constructor(props){
        super(props);
        this.state = {
            createFormOpen:false,
            createFreeFormOpen:false,
            uploadFormOpen:false,
            errors:false,
            search:{},
            newItem:{},
            position:INITIAL_POSITION,
            nomenclatureSearch:false,
            is_nonds: false,            
            sf: true,            
            newupload:{
                user_id: null,
                no_nds: 0,
                template_upload: null
            },
            hasMessage:false,
        };
        // this.closeCreateForm = this.closeCreateForm.bind(this);
        this.handleAddPosition = this.handleAddPosition.bind(this);
    }
    componentDidMount(){
        this.props.action('cheque', 'LIST');
        this.props.action('contractor', 'LIST');
        this.props.action('nomenclature', 'LIST');
        this.props.action('organisation', 'LIST');
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

    handleCreateFormDateFieldChange = (dates) => {
        let {newItem} = this.state;
        let dd = null;
        if(dates){
            if (dates[0]) newItem.period_start = VDate.truncDate(dates[0]);
            if (dates[1]) newItem.period_end = VDate.truncDate(dates[1], true);
        }
    }
    handleCreateFormFieldChange = (e, {name,value}) => {
        let {newItem} = this.state;

        if (name=='no_nds') {
            let v = !this.state.is_nonds;
            newItem.no_nds = v ? 1 : 0;
            this.setState({is_nonds: v});
        } else if (name=='sf') {
            let v = !this.state.sf;
            newItem.sf = v ? 1 : 0;
            this.setState({sf: v});
        } else {
            newItem[name] = value;
        }

        if(name=='organisation_id'){
            const {organisations} = this.props;
            const organisation = newItem.organisation_id?organisations.data.find( (item) => {return (item.id == newItem.organisation_id);}):null;
            newItem.organisation = organisation;

            newItem.no_nds = organisation.is_nonds ? 1 : 0;
            this.setState({is_nonds: organisation.is_nonds});
        }
        if(name=='contractor_id'){
            const {contractors} = this.props;
            newItem.data = newItem.data?newItem.data:{};
            newItem.data.contractor = contractors.data.find( (item) => {return (item.id == value);});
        }
        this.setState({newItem:newItem});
    }
    handleAddPosition = (e) => {
        const {nomenclatures} = this.props;
        let {newItem ,position} = this.state;
        if ( !position.nomenclature || !position.amount_from || !position.amount_to || !position.quantity_from || !position.quantity_to) return;
        newItem.data = newItem.data ? newItem.data : {};
        newItem.data.positions = newItem.data.positions ? newItem.data.positions : [];
        const newPosition = {...position};
        newItem.data.positions.push(newPosition);
        this.setState({newItem:newItem, position: INITIAL_POSITION});
    }
    handleRemovePosition = (i) => {
        const {newItem} = this.state;
        newItem.data.positions.splice(i,1);
        this.setState({newItem: newItem});
    }
    closeCreateForm = () => {
        this.setState({
            createFormOpen:false,
            errors:false,
            newItem:{}
        });
    }
    closeCreateFreeForm = () => {
        this.setState({
            createFreeFormOpen:false,
            errors:false,
            newItem:{}
        });
    }
    addItem = (e,data) => {
        const {newItem} = this.state;
        const {closeCreateForm} = this;
        this.props.action(
            'cheque_schedule',
            CREATE,
            newItem,
            (response)=>{
                closeCreateForm()
            },
            (error)=>{
                this.setState({
                    errors:error.response.data.errors
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
                    'cheque_schedule',
                    LIST,
                    search,
                    (response)=>{},
                    (error)=>{}
                )
            }
        );
    }
    handleSearchDate = (dates)=>{
        let {search} = this.state;
        let dd = null;
        if(dates && dates.length>1){
            dd = dates.map( d => {
                const date = new Date(d);
                return `${date.getFullYear()}-${date.getMonth()+1}-${date.getDate()+1}`;
            });
            search['dates'] = dd;
            this.setState({search: search},
                ()=>{
                    this.props.action(
                        'cheque_schedule',
                        LIST,
                        search,
                        (response)=>{},
                        (error)=>{}
                    )
                }
            );
        } else if(dates==null){
            search['dates'] = null;
            this.setState({search: search},
                ()=>{
                    this.props.action(
                        'cheque_schedule',
                        LIST,
                        search,
                        (response)=>{},
                        (error)=>{}
                    )
                }
            );
        }
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
    render() {
        const {contractors,cheque_schedule,organisations,expensetypes,nomenclatures,users} = this.props;
        const {newItem, createFormOpen, createFreeFormOpen, uploadFormOpen, errors, search, nomenclatureSearch} = this.state;
        const {organisation, user_id} = newItem;
        const monthFirstDay = () => {
            var today = new Date();
            const ret = new Date(today.getFullYear(), today.getMonth(), 1);
            return ret;
        }
        const expensetypeOptions = (expensetypes.fetching)?[]:expensetypes.data.map( (item,i) => {
            return {
                key: item.id,
                value: item.id,
                text: item.name
            }
        });
        const organisationSearchOptions = organisations.data.map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.limit
            }
        })
        const organisationOptions = organisations.data.filter( (item,i) => { return (item.expense_type_id == newItem.expense_type_id) }).map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.limit
            }
        })
        const nomenclatureOptions = nomenclatures.data.map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: `${trans('messages.nds')}: ${item.nds}%`
            }
        })
        const nomenclatureResults = nomenclatures.data.filter( (item,i) => { const pattern = new RegExp(`.*${(nomenclatureSearch!==false)?nomenclatureSearch:''}.*`,'ig'); return pattern.test(item.name);}).map( (item,i) => {
            return{
                key: i,
                value: item.id,
                title: item.name,
                nds: item.nds,
                description: `${trans('messages.nds')}: ${item.nds}%`
            };
        })
        const userOptions = users.data.map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.email
            }
        })
        const contractorOptions = (contractors.fetching)?[]:contractors.data.filter( item=>{ if(user_id==undefined || user_id == null)return true; return (item.user_id == user_id)  } ).map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.email
            }
        })

        let {totalAmount,totalNds,totalQuantity,error} = {totalAmount:0, totalNds:0, totalQuantity:0, error: false};
        const contractor = (newItem.data&&newItem.data.contractor)?newItem.data.contractor:null;
        const monthFD = monthFirstDay();

        const newFreeCheque = () => {
            const {newItem} = this.state;
            newItem.type='free';
            this.setState({createFreeFormOpen:true, newItem: newItem});
        }
        const newScheduleCheque = () => {
            const {newItem} = this.state;
            newItem.type = 'schedule';
            this.setState({createFormOpen:true, newItem: newItem})
        }
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.cheque_schedule.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                    <Menu.Item>
                        <a href="/cheque_schedules/template"><Icon name="download"/>Скачать шаблон заявки по расписанию</a>
                    </Menu.Item>
                    <Menu.Item>
                        <a href="/cheque_schedules/template_date"><Icon name="download"/>Скачать шаблон заявки на дату (new)</a>
                    </Menu.Item>
                    <Menu.Item>
                        {
                            auth.can.manager
                            ? <div>
                                <Button icon primary onClick={()=>{this.setState({uploadFormOpen:true})}}>
                                    <Icon.Group>
                                        <Icon name="file outline"></Icon>
                                        <Icon corner="top right" name="plus"></Icon>
                                    </Icon.Group>
                                    Загрузить заявку
                                </Button>
                                <Modal closeIcon={<Icon name="close" onClick={()=>{this.setState({uploadFormOpen:false})}}/>} open={uploadFormOpen} centered={true}>
                                    <Header icon='plus' content="Загрузить заявку" />
                                    <Modal.Content>
                                        <Form>
                                            <Form.Group>
                                                <Form.Select label="Пользователь" onChange={(e, {name, value}) => { this.setState({newupload:{user_id: value}});}} options={userOptions} name="user_id" placeholder={trans('messages.users.title')}/>
                                                <Form.Checkbox style={{marginTop: 26 + 'px'}} name="no_nds" label="Без НДС" onChange={(e, {name, value}) => { this.setState({no_nds: value});}} value="1" />
                                            </Form.Group>
                                            {
                                                this.state.newupload.user_id
                                                ?<Form.Group>
                                                    <FileUpload label="Файл по шаблону" name="template_upload" url="/cheque_schedule" onBefore={(p) => {p.append('user_id', this.state.newupload.user_id); if (this.state.no_nds) { p.append('no_nds', this.state.no_nds); } ;}} onUploaded={(response) => {console.log('upload',response);document.location.reload();}} onFailed={(response) => {console.log('upload',response);document.location.reload();}}/>
                                                </Form.Group>
                                                :null
                                            }

                                        </Form>
                                    </Modal.Content>
                                </Modal>
                            </div>
                            : null
                        }

                        {
                            ((auth.role == 'user') && (auth.limit_current >= auth.limit_total)) 
                                ?
                            <Popup position="bottom right" trigger={<Button icon primary className="like_disabled">Загрузить заявку</Button>}>
                                Вы выбрали допустимый лимит. Дальнейшая печать не возможна
                            </Popup>
                                :
                            <div>
                                <Label inline>Загрузить заявку</Label>
                                <FileUpload name="template_upload" url="/cheque_schedule" onUploaded={(response) => {document.location.reload();}} onFailed={(response) => {document.location.reload();}} />
                            </div>
                        }
                    </Menu.Item>
                    <Menu.Item>
                        {
                            ((auth.role == 'user') && (auth.limit_current >= auth.limit_total)) 
                                ?
                            <Popup position="bottom right" trigger={<Button icon primary className="like_disabled"><Icon.Group><Icon name="money bill alternate outline"></Icon><Icon corner="top right" name="plus"></Icon></Icon.Group>Чеки по расписанию</Button>}>
                                Вы выбрали допустимый лимит. Дальнейшая печать не возможна
                            </Popup>
                                :
                            <Button icon primary onClick={()=>{this.setState({createFormOpen:true})}}><Icon.Group>
                                <Icon name="money bill alternate outline"></Icon>
                                <Icon corner="top right" name="plus"></Icon>
                            </Icon.Group>Чеки по расписанию</Button>
                        }
                        {/* Scheduled*/}
                        <Modal closeIcon={<Icon name="close" onClick={()=>{this.setState({createFormOpen:false})}}/>} open={createFormOpen} centered={false}>
                            <Header icon='plus' content={trans('messages.add')} />
                            <Modal.Content>
                                <Form>
                                    <Form.Group>
                                        <SemanticDatepicker
                                            format='DD.MM.YYYY'
                                            width={8}
                                            locale={ptLocale}
                                            onDateChange={this.handleCreateFormDateFieldChange}
                                            required
                                            label="Период действия"
                                            error={(errors && (errors.period_start || errors.period_end)) ? { content:(errors.period_start || errors.period_end)[0], pointing:'below'} : null}
                                            type="range"/>
                                        <Form.Select required name="periodic" error={(errors && errors.periodic) ? {content:errors.periodic[0],pointing:'below'}:null} options={window.cheques.periodicOptions} value={newItem.periodic} label="Периодичность" onChange={this.handleCreateFormFieldChange}/>
                                    </Form.Group>
                                </Form>
                                <Form>
                                    <Form.Group>
                                        <Form.Select width={8} search required name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={expensetypeOptions} value={newItem.expense_type_id} label={trans('messages.expensetypes.title')} onChange={this.handleCreateFormFieldChange}/>
                                        <Form.Select width={8} search key={'cheque_organisation_id'} required name="organisation_id" error={(errors && errors.organisation_id)?{content:errors.organisation_id[0],pointing:'below'}:null} options={organisationOptions}  label={trans('messages.organisations.title')} onChange={this.handleCreateFormFieldChange}/>
                                    </Form.Group>
                                </Form>
                                <Divider/>
                                <Container className="cheque">
                                    {organisation?<Header align="center">
                                        <Header.Content>{organisation.name}</Header.Content>
                                        <Header.Subheader>{trans('messages.organisations.inn')}: <b>{organisation.data.inn}</b>, {trans('messages.organisations.kpp')}: <b>{organisation.data.kpp}</b>,  {organisation.data.address}</Header.Subheader>
                                    </Header>:null}
                                    <div align="center">
                                        <Table collapsing compact size="small">
                                            <Table.Body>
                                                { (newItem.data && newItem.data.positions) ?
                                                    newItem.data.positions.map( (position,i) => {
                                                        const nds = parseFloat(position.nds || organisation.data.nds);
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
                                                    <Table.HeaderCell width={8} textAlign="right" style={{ "position": "relative" }}>
                                                        <Amount value={totalAmount}/><Icon name="ruble"/>
                                                        {((window.auth.id == 29) && (totalAmount>100000))?
                                                        <div style={{ 
                                                            "background-color": "#db2828", 
                                                            "color": "#fff", 
                                                            "position": "absolute", 
                                                            "left": "100%", 
                                                            "top": "50%", 
                                                            "margin-top": "-12px",
                                                            "text-align": "left", 
                                                            "padding": "6px 0px 6px 6px", 
                                                            "white-space": "nowrap",
                                                            "line-height": "1",
                                                            "border-radius": ".28571429rem",
                                                            "margin-left": "10px",
                                                        }}>
                                                            <i style={{
                                                                "position": "absolute",
                                                                "display": "block",
                                                                "width": "0",
                                                                "height": "0",
                                                                "border-top": "4px solid transparent",
                                                                "border-bottom": "4px solid transparent",
                                                                "border-right": "4px solid #db2828",
                                                                "top": "50%",
                                                                "left": "-4px",
                                                                "transform": "translateY(-50%)",
                                                            }}/>
                                                            Сумма чека превышает 100 000
                                                            <Icon name="ruble"/>
                                                        </div>:null}
                                                    </Table.HeaderCell>
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

                                            {/* <Form.Input width={8} required name="nomenclature" value={this.state.position.nomenclature} label={trans('messages.nomenclatures.title')} onChange={this.handlePositionFieldChange}/> */}

                                            <Form.Input width={4} required type="number" step={0.01} name="amount_from" value={this.state.position.amount_from} label={trans('messages.costs')} icon="ruble" onChange={this.handlePositionFieldChange}/>
                                            {/* <Form.Input width={4} required type="number" step={0.01} name="amount_to" value={this.state.position.amount_to} label={trans('messages.costs_to')} icon="ruble" onChange={this.handlePositionFieldChange}/> */}
                                            <Form.Input
                                                width={3} required type="number" step={0.01} name="quantity_from" value={this.state.position.quantity_from} label={trans('messages.quantity')}
                                                onChange={this.handlePositionFieldChange}
                                                onBlur={this.handleAddPosition}
                                                />
                                            {/* <Form.Input
                                                width={3} required type="number" step={0.01} name="quantity_to" value={this.state.position.quantity_to} label={trans('messages.quantity_to')}
                                                onChange={this.handlePositionFieldChange}
                                                onBlur={this.handleAddPosition}
                                                /> */}
                                            <Form.Button width={1} disabled={error} type="button" step={1} label="&nbsp;" onClick={this.handleAddPosition} color="orange" icon><Icon name="plus"/></Form.Button>
                                        </Form.Group>
                                    </Form>
                                    {contractor?<Header align="center">
                                        <Header.Content>{contractor.name}</Header.Content>
                                        <Header.Subheader>{trans('messages.organisations.inn')}: <b>{contractor.inn}</b>, {trans('messages.organisations.kpp')}: <b>{contractor.kpp}</b>,  {contractor.address}</Header.Subheader>
                                    </Header>:null}
                                </Container>
                                <Divider/>
                                <Form>
                                    <Form.Group>
                                        {auth.can.admin?<Form.Select name="user_id" onChange={this.handleCreateFormFieldChange} options={userOptions} label={trans('messages.users.title')}/>:null}
                                        <Form.Select name="contractor_id" onChange={this.handleCreateFormFieldChange} options={contractorOptions} label={trans('messages.contractors.title')}/>
                                        
                                        <Form.Checkbox style={{marginTop: 26 + 'px'}} name="no_nds" label="Без НДС" onChange={this.handleCreateFormFieldChange} value="1" checked={this.state.is_nonds} />
                                        {/*<Form.Checkbox style={{marginTop: 26 + 'px'}} name="sf" label="Нужна счет-фактура" onChange={this.handleCreateFormFieldChange} value="1" checked={this.state.sf} />*/}
                                    </Form.Group>
                                </Form>
                                {/*<Divider/>
                                    <Form>
                                    <Form.Checkbox name="upd" onChange={this.handleCreateFormFieldChange} label={trans('messages.cheque_schedule.upd')}/>
                                    <Form.Checkbox name="sf" onChange={this.handleCreateFormFieldChange} label={trans('messages.cheque_schedule.sf')}/>
                                </Form>*/}

                                {error?<Message negative>
                                    <Icon name="warning sign"/> {trans('messages.organisations.limit_reached')}
                                </Message>:null}
                            </Modal.Content>
                            <Modal.Actions>
                                <Button negative onClick={this.closeCreateForm}>
                                    <Icon name='x' /> {trans('messages.cancel')}
                                </Button>
                                {
                                    (this.state.newItem && this.state.newItem.data && this.state.newItem.data.positions && this.state.newItem.data.positions.length)
                                    ?<Button positive onClick={this.addItem} disabled={ ((window.auth.id == 29) && (totalAmount>100000)) }><Icon name='checkmark' />  {trans('messages.save')}</Button>
                                    :null
                                }

                            </Modal.Actions>
                        </Modal>
                    </Menu.Item>
                    <Menu.Item>
                        {
                            ((auth.role == 'user') && (auth.limit_current >= auth.limit_total)) 
                                ?
                            <Popup position="bottom right" trigger={<Button icon primary className="like_disabled"><Icon.Group><Icon name="money bill alternate outline"></Icon><Icon corner="top right" name="plus"></Icon></Icon.Group>Розничные чеки</Button>}>
                                Вы выбрали допустимый лимит. Дальнейшая печать не возможна
                            </Popup>
                                :
                            <Button icon primary onClick={newFreeCheque}><Icon.Group>
                                <Icon name="money bill alternate outline"></Icon>
                                <Icon corner="top right" name="plus"></Icon>
                            </Icon.Group>Розничные чеки</Button>
                        }
                    {/* Free cheque */}
                        <Modal closeIcon={<Icon name="close" onClick={()=>{this.setState({createFreeFormOpen:false})}}/>} open={createFreeFormOpen} centered={false}>
                            <Header icon='plus' content={trans('messages.add')} />
                            <Modal.Content>
                                <Form>
                                    <Form.Group>
                                        <SemanticDatepicker
                                                    format='DD.MM.YYYY'
                                                    width={8}
                                                    locale={ptLocale}
                                                    onDateChange={this.handleCreateFormDateFieldChange}
                                                    required
                                                    label="Период действия"
                                                    type="range"/>
                                                <Form.Select required name="periodic" error={(errors && errors.periodic)?{content:errors.periodic[0],pointing:'below'}:null} options={window.cheques.periodicOptions} value={newItem.periodic} label="Периодичность" onChange={this.handleCreateFormFieldChange}/>
                                    </Form.Group>
                                </Form>
                                <Form>
                                    <Form.Group>
                                        <Form.Select width={8} search required name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={expensetypeOptions} value={newItem.expense_type_id} label={trans('messages.expensetypes.title')} onChange={this.handleCreateFormFieldChange}/>
                                        <Form.Select width={8} search key={'cheque_organisation_id'} required name="organisation_id" error={(errors && errors.organisation_id)?{content:errors.organisation_id[0],pointing:'below'}:null} options={organisationOptions}  label={trans('messages.organisations.title')} onChange={this.handleCreateFormFieldChange}/>
                                    </Form.Group>
                                </Form>
                                <Divider/>
                                <Container className="cheque">
                                    {organisation?<Header align="center">
                                        <Header.Content>{organisation.name}</Header.Content>
                                        <Header.Subheader>{trans('messages.organisations.inn')}: <b>{organisation.data.inn}</b>, {trans('messages.organisations.kpp')}: <b>{organisation.data.kpp}</b>,  {organisation.data.address}</Header.Subheader>
                                    </Header>:null}
                                    <Table collapsing size="small" align="center">
                                        <Table.Body>
                                            {newItem.data&&newItem.data.positions?newItem.data.positions.map( (position,i) => {
                                                console.log(position);
                                                const nds = parseFloat(position.nds || organisation.data.nds);
                                                totalAmount += position.amount_to*position.quantity_to;
                                                totalNds += calculateNds (position.quantity_to*position.amount_to, 100*nds);
                                                totalQuantity += position.quantity_to;
                                                error = (organisation!=undefined && organisation.limit<totalAmount) ;
                                                return (<Table.Row key={i}>
                                                    <Table.Cell width={12}>{position.nomenclature}({trans('messages.nds')} {nds*100}%)</Table.Cell>
                                                    <Table.Cell width={4} textAlign="right">
                                                        {position.quantity_to}x{position.amount_to}<Icon name="ruble"/><br/>=<strong>{position.amount_to*position.quantity_to}<Icon name="ruble"/></strong>
                                                        <a href="#" onClick={()=>this.handleRemovePosition(i)}><Icon name="x"/></a>
                                                    </Table.Cell>
                                                </Table.Row>);
                                            }):null}
                                        </Table.Body>
                                        {(totalQuantity>0)?<Table.Footer>
                                            <Table.Row>
                                                <Table.Cell width={8} textAlign="right">{trans('messages.totals.amount')}</Table.Cell>
                                                <Table.HeaderCell width={8} textAlign="right" style={{ "position": "relative" }}>
                                                    <Amount value={totalAmount}/><Icon name="ruble"/>
                                                    {((window.auth.id == 29) && (totalAmount>100000))?
                                                        <div style={{ 
                                                            "background-color": "#db2828", 
                                                            "color": "#fff", 
                                                            "position": "absolute", 
                                                            "left": "100%", 
                                                            "top": "50%", 
                                                            "margin-top": "-12px",
                                                            "text-align": "left", 
                                                            "padding": "6px 0px 6px 6px", 
                                                            "white-space": "nowrap",
                                                            "line-height": "1",
                                                            "border-radius": ".28571429rem",
                                                            "margin-left": "10px",
                                                        }}>
                                                            <i style={{
                                                                "position": "absolute",
                                                                "display": "block",
                                                                "width": "0",
                                                                "height": "0",
                                                                "border-top": "4px solid transparent",
                                                                "border-bottom": "4px solid transparent",
                                                                "border-right": "4px solid #db2828",
                                                                "top": "50%",
                                                                "left": "-4px",
                                                                "transform": "translateY(-50%)",
                                                            }}/>
                                                            Сумма чека может превысить 100 000
                                                            <Icon name="ruble"/>
                                                        </div>:null}
                                                </Table.HeaderCell>
                                            </Table.Row>
                                            <Table.Row>
                                                <Table.Cell width={8} textAlign="right">{trans('messages.totals.nds')}</Table.Cell>
                                                <Table.HeaderCell width={8} textAlign="right"><Amount value={totalNds}/><Icon name="ruble"/></Table.HeaderCell>
                                            </Table.Row>
                                        </Table.Footer>:null}
                                    </Table>
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

                                            {/* <Form.Input width={8} required name="nomenclature" value={this.state.position.nomenclature} label={trans('messages.nomenclatures.title')} onChange={this.handlePositionFieldChange}/> */}

                                            <Form.Input width={4} required type="number" step={1} name="amount_from" value={this.state.position.amount_from} label={trans('messages.costs_from')} icon="ruble" onChange={this.handlePositionFieldChange}/>
                                            <Form.Input width={4} required type="number" step={1} name="amount_to" value={this.state.position.amount_to} label={trans('messages.costs_to')} icon="ruble" onChange={this.handlePositionFieldChange}/>
                                            <Form.Input
                                                width={3} required type="number" step={1} name="quantity_from" value={this.state.position.quantity_from} label={trans('messages.quantity_from')}
                                                onChange={this.handlePositionFieldChange}
                                                onBlur={this.handleAddPosition}
                                                />
                                            <Form.Input
                                                width={3} required type="number" step={1} name="quantity_to" value={this.state.position.quantity_to} label={trans('messages.quantity_to')}
                                                onChange={this.handlePositionFieldChange}
                                                onBlur={this.handleAddPosition}
                                                />
                                            <Form.Button width={1} disabled={error} type="button" step={1} label="&nbsp;" onClick={this.handleAddPosition} color="orange" icon><Icon name="plus"/></Form.Button>
                                        </Form.Group>
                                    </Form>
                                    {contractor?<Header align="center">
                                        <Header.Content>{contractor.name}</Header.Content>
                                        <Header.Subheader>{trans('messages.organisations.inn')}: <b>{contractor.inn}</b>, {trans('messages.organisations.kpp')}: <b>{contractor.kpp}</b>,  {contractor.address}</Header.Subheader>
                                    </Header>:null}
                                </Container>
                                <Divider/>

                                <Divider/>
                                <Form>
                                    <input type="hidden" name="upd" value="off"/>
                                    {/*<input type="hidden" name="sf" label={trans('messages.cheque_schedule.sf')} value="off"/>*/}
                                    {/*<Form.Checkbox name="upd" onChange={this.handleCreateFormFieldChange} label={trans('messages.cheque_schedule.upd')}/>
                                <Form.Checkbox name="sf" onChange={this.handleCreateFormFieldChange} label={trans('messages.cheque_schedule.sf')}/>*/}
                                </Form>

                                {error?<Message negative>
                                    <Icon name="warning sign"/> {trans('messages.organisations.limit_reached')}
                                </Message>:null}
                            </Modal.Content>
                            <Modal.Actions>
                                <Button negative onClick={this.closeCreateFreeForm}>
                                    <Icon name='x' /> {trans('messages.cancel')}
                                </Button>
                                {
                                    (this.state.newItem && this.state.newItem.data && this.state.newItem.data.positions && this.state.newItem.data.positions.length)
                                    ?<Button positive onClick={this.addItem} disabled={ ((window.auth.id == 29) && (totalAmount>100000)) }><Icon name='checkmark' />  {trans('messages.save')}</Button>
                                    :null
                                }

                            </Modal.Actions>
                        </Modal>
                    </Menu.Item>
                </Menu.Menu>
            </Menu>
            <Form>
                <Form.Group>
                    {/*
                    <Form.Input placeholder={trans('messages.search')} width={8} icon="search" onChange={this.handleSearch} name="search" defaultValue={(search.search)?search.search:''}/>
                    {auth.can.admin ? <Form.Select multiple onChange={this.handleSearch} options={userOptions} name="user_id" placeholder={trans('messages.users.title')}/> : null}
                    {auth.can.admin ? <Form.Select multiple onChange={this.handleSearch} options={organisationSearchOptions} name="organisation_id" placeholder={trans('messages.organisations.title')}/> : null}
                    <SemanticDatepicker
                            format='DD.MM.YYYY'
                            width={8}
                            locale={ptLocale}
                            onDateChange={this.handleSearchDate}
                            type="range"/>*/}

                </Form.Group>
            </Form>

            {window.message?
                <Message warning>
                    <MessageHeader>{ window.message }</MessageHeader>
                    <Button style={{ 'margin-top': '10px' }} positive onClick={() => { window.message = ''; this.setState({hasMessage:false}) }}>
                        <Icon name='x' /> Ok
                    </Button>
                </Message>
            : null}

            <Divider horizontal>{trans('messages.list')}</Divider>
            {
                cheque_schedule.fetching
                ?<Dimmer active inverted><Loader /></Dimmer>
                :<Table celled selectable>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell>№</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.cheque_schedule.created_at')}</Table.HeaderCell>
                            <Table.HeaderCell>Клиент</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.cheque_schedule.organisation')}</Table.HeaderCell>
                            <Table.HeaderCell>Вид расходов</Table.HeaderCell>
                            <Table.HeaderCell>Период с </Table.HeaderCell>
                            <Table.HeaderCell>по</Table.HeaderCell>
                            <Table.HeaderCell>Периодичность</Table.HeaderCell>
                            <Table.HeaderCell>Все чеки</Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {cheque_schedule.data.map( (item,i) => { return (<ChequeSchedule key={i} item={item} contractors={contractors} organisations={organisations} expensetypes={expensetypes} nomenclatures={nomenclatures} userOptions={userOptions}/>); } )}
                    </Table.Body>
                </Table>
            }
        </Segment>
    }
}

export default connect(
    (state,ownProps)=>{
        return {
            contractors:state.contractors,
            cheque_schedule:state.cheque_schedule,
            organisations:state.organisations,
            nomenclatures:state.nomenclatures,
            users:state.users,
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
)(ChequeSchedules);
