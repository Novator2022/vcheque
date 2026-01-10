import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Container, Form, Label, Button, Menu,Divider,Dimmer, Dropdown,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Table, Message, Grid, Pagination, Popup, FormGroup, FormField } from 'semantic-ui-react';
import SemanticDatepicker from 'react-semantic-ui-datepickers';
import ptLocale from 'react-semantic-ui-datepickers/dist/locales/ru-RU';
import 'react-semantic-ui-datepickers/dist/react-semantic-ui-datepickers.css';
import numeral from 'numeral';

import {modelRest, shellReload} from '../actions';
import {trans, USER_ROLES,CREATE,UPDATE, USER_ROLES_SEARCH,LIST} from '../constants';
import Cheque from '../components/Cheque';
import {Amount} from '../components/simple';
import {paginate} from '../helpers/paginator';
import {VDate} from '../helpers/dates';

const INITIAL_POSITION = {
    nomenclature_id:'',
    nomenclature:'',
    name: '',
    amount:100,
    quantity:1,
    nds:0.2
};

class Cheques extends Component {
    constructor(props){
        super(props);
        this.state = {
            createFormOpen:false,
            errors:false,
            search:{},
            newItem:{},
            position:INITIAL_POSITION,
            nomenclatureSearch:false,
            page: 1,
            paginating: true,
            confirm_active: false,
            is_nonds: false,
            email: false,
            sf: 1,
            selected_cheques: [],
            select_all_checked: false,
            printing_many: false,
            refresh: false,
        };
        // this.closeCreateForm = this.closeCreateForm.bind(this);
        this.handleAddPosition = this.handleAddPosition.bind(this);
    }
    shouldComponentUpdate(nextProps, nextState) {
        return true;
    }
    componentDidMount(){
        this.props.action('cheque', 'LIST');
        this.props.action('contractor', 'LIST');
        this.props.action('nomenclature', 'LIST');
        this.props.action('category', 'LIST');
        this.props.action('organisation', 'LIST');
    }
    handlePositionFieldChange = (e, {name,value}) => {
        let {position} = this.state;
        const {nomenclatures} = this.props;
        position[name]=value;
        if(name == 'nomenclature_id') {
            const nomenclature = nomenclatures.data.find( item => {return (item.id == value)} );
            position.name = nomenclature.name;
            position.nds = nomenclature.nds;
        } else if(name == 'amount') {
            position[name] = value.replace(/,/ig, ".");
        } else if(name == 'quantity') {
            position[name] = value.replace(/,/ig, ".");
        }
        this.setState({position:position});
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
        } else if (name=='email') {
            newItem.email = value;
            this.setState({email: value});
        } else {
            newItem[name] = value;
        }

        // console.log(newItem);

        if(name=='organisation_id'){
            const {organisations} = this.props;
            const organisation = newItem.organisation_id?organisations.data.find( (item) => { return (item.id == newItem.organisation_id); }):null;
            newItem.organisation = organisation;

            newItem.no_nds = organisation.is_nonds ? 1 : 0;
            newItem.sf = this.state.sf;
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
        if ( !position.nomenclature || !position.amount || !position.quantity) return;
        newItem.data = newItem.data ? newItem.data : {};
        newItem.data.positions = newItem.data.positions ? newItem.data.positions : [];
        const newPosition = {...position};
        newItem.data.positions.push(newPosition);
        this.setState({newItem:newItem});
    }
    handleRemovePosition = (i) => {
        const {newItem} = this.state;
        newItem.data.positions.splice(i,1);
        this.setState({newItem: newItem});
    }
    closeCreateForm = () => {
        this.setState({
            confirm_active:false,
            createFormOpen:false,
            errors:false,
            newItem:{}
        });
    }
    addItem = (e,data) => {
        const {newItem} = this.state;
        const {closeCreateForm} = this;
        this.props.action(
            'cheque',
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
    askConfirm = (e,data) => {
        this.setState({
            confirm_active: true
        })
    }
    updateItem = (e, item) => {
        const {handleSearch} = this;
        this.props.action(
            'cheque',
            UPDATE,
            item,
            (response)=>{
                console.log('updated cheque' , response, item);
                // handleSearch();
            },
            (error)=>{
                this.setState({
                    errors:error.response.data
                })
            }
        );
    }

    updateSelectAll = (e, cheques) => {
        const { selected_cheques, select_all_checked, selected_cheques_cnt, refresh, printing_many } = this.state;
        let checked = !select_all_checked;

        for (var i = 0; i < cheques.cheques.length; i++) {
            let value = cheques.cheques[i];
            const index = selected_cheques.indexOf(value);
            if (index > -1) { // only splice array when item is found
                if (!checked) {
                    selected_cheques.splice(index, 1); // 2nd parameter means remove one item only
                }
            } else {
                if (checked) {
                    selected_cheques.push(value);
                }
            }
        }

        window.selected_cheques = selected_cheques;
        this.setState({select_all_checked: checked});
        this.setState({selected_cheques: selected_cheques});
        this.setState({selected_cheques_cnt: selected_cheques.length });
        this.setState({refresh: selected_cheques.length});        
    }

    printSelected = () => {
        this.setState({ printing_many: true });

        let queue_id = 1;
        const url = "/cheques/print_queue/" + queue_id;

        const requestMetadata = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.getElementsByTagName("meta")['csrf-token'].content
            },
            body: JSON.stringify({ cheques: window.selected_cheques })
        };

        fetch(url, requestMetadata)
            .then(res => res.json())
            .then(result => {
                this.setState({ printing_many: false });
                console.log(result);
            });
    }

    updateSelected = (new_selected_cheques) => {
        const {selected_cheques} = this.state;
        this.setState({selected_cheques: new_selected_cheques});
        this.setState({selected_cheques_cnt: new_selected_cheques.length});
        this.setState({refresh: new_selected_cheques.length});
    }

    clearSelected = () => {
        const {selected_cheques} = this.state;
        this.setState({selected_cheques: []});
        this.setState({selected_cheques_cnt: 0});
        this.setState({select_all_checked: false});
        this.setState({refresh: 0});
        window.selected_cheques = [];
    }

    handleSearch = (e,{name,value})=>{
        let {search} = this.state;

        if (name) {
            search[name] = value;
            this.setState({search: search},
                ()=>{
                    this.props.action(
                        'cheque',
                        LIST,
                        search,
                        (response)=>{},
                        (error)=>{}
                    )
                }
            );
        } else {
            this.props.action(
                'cheque',
                LIST,
                search,
                (response)=>{},
                (error)=>{}
            )
        }
    }
    handleSearchDate = (dates)=>{
        let {search} = this.state;
        if (dates==null) {
            search['dates'] = '';
            this.setState({search: search},
                ()=>{
                    this.props.action(
                        'cheque',
                        LIST,
                        search,
                        (response)=>{},
                        (error)=>{}
                    )
                }
            );
        } else if (dates && dates.length>1) {
            search['dates'] = [
                VDate.truncDate(dates[0]),
                VDate.truncDate(dates[1], true)
            ];
            this.setState({search: search},
                ()=>{
                    this.props.action(
                        'cheque',
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
        const {contractors,cheques,organisations,categories,expensetypes,nomenclatures,users} = this.props;
        const {newItem,createFormOpen,errors,search,nomenclatureSearch,selected_cheques,selected_cheques_cnt,select_all_checked,refresh,printing_many} = this.state;
        const {organisation,user_id} = newItem;
        const monthFirstDay = () => {
            var today = new Date();
            const ret = new Date(today.getFullYear(), today.getMonth(), 1);
            return ret;
        }
        const snoTypes = [{
            key: 'osno',
            value: 'osno',
            text: 'ОСНО'
        }, {
            key: 'usn',
            value: 'usn',
            text: 'УСН'
        }];
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
        // const organisationOptions = organisations.data.filter( (item,i) => { return (item.expense_type_id == newItem.expense_type_id) }).map( (item,i) => {
        //     return {
        //         key: i,
        //         value: item.id,
        //         text: item.name,
        //         description: item.limit
        //     }
        // })
        const organisationOptions = organisations.data.filter( (item,i) => { return (((item.is_nonds == 1) && (newItem.sno == 'usn')) || ((item.is_nonds == 0) && (newItem.sno == 'osno'))) }).map( (item,i) => {
            return {
                key: i,
                value: item.id,
                text: item.name,
                description: item.limit
            }
        })
		const categoriesOptions = categories.data.map( (item,i) => {
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
        const nomenclatureResults = nomenclatures.data.filter( (item,i) => {
            const pattern = new RegExp(`.*${(nomenclatureSearch!==false) ? escapeRegExp(nomenclatureSearch) : ''}.*`,'ig');
            return pattern.test(item.name);
        }).map( (item,i) => {
            return{
                key: i,
                value: item.id,
                title: item.name,
                nds: item.nds,
                description: `${trans('messages.nds')}: ${item.nds}%`
            };
        });
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

        const pagination = paginate(cheques.data, this.state.page);
        const chequeTypesOptions = Object.keys(window.cheques.types).map(i => {
            return {
                key: i,
                value: i,
                text: window.cheques.types[i]
            };
        });
        const chequeStatusesOptions = Object.keys(window.cheques.statuses).map(i => {
            return {
                key: i,
                value: i,
                text: window.cheques.statuses[i]
            };
        });

        // console.log(pagination.data.map( (item,i) => { return item.id; }));

        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.cheques.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                    <Menu.Item>
                        <Button.Group>
                            <Button icon basic onClick={()=>{shellReload()}}><Icon name="refresh"></Icon> Перезапустить очередь</Button>
                            {
                                ((auth.role == 'user') && (auth.limit_current >= auth.limit_total)) 
                                ?
                                <Popup position="bottom right" trigger={<Button icon primary className="like_disabled"><Icon.Group><Icon name="money bill alternate outline"></Icon><Icon corner="top right" name="plus"></Icon></Icon.Group></Button>}>
                                    Вы выбрали допустимый лимит. Дальнейшая печать не возможна
                                </Popup>
                                : 
                                <Button icon primary onClick={()=>{this.setState({createFormOpen:true})}} data-tooltip="Создать новый чек" data-position="left center">
                                    <Icon.Group>
                                        <Icon name="money bill alternate outline"></Icon>
                                        <Icon corner="top right" name="plus"></Icon>
                                    </Icon.Group>
                                </Button>
                            }
                        </Button.Group>

                        <Modal closeIcon={<Icon name="close" onClick={()=>{this.setState({createFormOpen:false})}}/>} open={createFormOpen} centered={false} title="Создать новый чек">
                            <Header icon='plus' content={trans('messages.add')} />
                            <Modal.Content>
                                {this.state.errors && this.state.errors.balance?<Message negative>
                                    <Icon name="warning sign"/> {this.state.errors.balance}
                                </Message>:null}
                                {this.state.errors && this.state.errors.limit?<Message negative>
                                    <Icon name="warning sign"/> {this.state.errors.limit}
                                </Message>:null}

                                <Form>
                                    <Form.Group>
                                        <Form.Select width={4} search required name="sno" error={(errors && errors.sno)?{content:errors.sno[0],pointing:'below'}:null} options={snoTypes} value={newItem.sno} label='Система налогооблажения' onChange={this.handleCreateFormFieldChange}/>
                                        {/*<Form.Select width={8} search required name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={expensetypeOptions} value={newItem.expense_type_id} label={trans('messages.expensetypes.title')} onChange={this.handleCreateFormFieldChange}/>*/}
                                        <Form.Select width={8} key={'cheque_organisation_id'} required name="organisation_id" error={(errors && errors.organisation_id)?{content:errors.organisation_id[0],pointing:'below'}:null} options={organisationOptions} closeOnSelect={true} label={trans('messages.organisations.title')} onChange={this.handleCreateFormFieldChange}/>
                                        <Form.Select width={8} search key={'category_id'} required name="category_id" error={(errors && errors.category_id)?{content:errors.category_id[0],pointing:'below'}:null} options={categoriesOptions} value={newItem.category_id} label={trans('messages.categories.title')} onChange={this.handleCreateFormFieldChange}/>
                                    </Form.Group>
                                </Form>
                                <Divider/>
                                <Container className="cheque">
                                    {organisation?<Header align="center">
                                        <Header.Content>{organisation.name}</Header.Content>
                                        <Header.Subheader>{trans('messages.organisations.inn')}: <b>{organisation.data.inn}</b>, {trans('messages.organisations.kpp')}: <b>{organisation.data.kpp}</b>,  {organisation.data.address}</Header.Subheader>
                                    </Header>:null}
                                    <div align="center">
                                        <Table collapsing size="small" align="center">
                                            <Table.Body>
                                                {newItem.data&&newItem.data.positions?newItem.data.positions.map( (position,i) => {
                                                    const nds = parseFloat(position.nds || organisation.data.nds);
                                                    totalAmount += parseFloat(position.amount) * parseFloat(position.quantity);
                                                    totalNds += calculateNds(parseFloat(position.amount) * parseFloat(position.quantity), 100*nds);
                                                    totalQuantity += parseFloat(isNaN(position.quantity) ? position.quantity.replace(/,/, '.') : position.quantity);
                                                    error = false; //(organisation!=undefined && organisation.limit<totalAmount) ;
                                                    return (<Table.Row key={i}>
                                                        <Table.Cell width={12}>{position.nomenclature} ({trans('messages.nds')} {nds*100}%)</Table.Cell>
                                                        <Table.Cell width={4} textAlign="right">
                                                            {position.quantity}x{position.amount}<Icon name="ruble"/><br/>=<strong>{parseFloat(position.amount*position.quantity).toFixed(2)}<Icon name="ruble"/></strong>
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
                                                    input={{ maxLength: 128 }} 
                                                    results={nomenclatureResults}
                                                    />
                                            </Form.Field>

                                            {/* <Form.Input width={8} required name="nomenclature" value={this.state.position.nomenclature} label={trans('messages.nomenclatures.title')} onChange={this.handlePositionFieldChange}/> */}

                                            <Form.Input width={4} required type="number" step={1} name="amount" value={this.state.position.amount} label={trans('messages.costs')} icon="ruble" onChange={this.handlePositionFieldChange}/>
                                            <Form.Input
                                                width={3} step={0.01}
                                                required type="number" name="quantity" value={this.state.position.quantity} label="Количество"
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
                                <Form>
                                    <Form.Group>
                                        {auth.can.admin?<Form.Select name="user_id" onChange={this.handleCreateFormFieldChange} options={userOptions} label={trans('messages.users.title')}/>:null}
                                        <Form.Select name="contractor_id" onChange={this.handleCreateFormFieldChange} options={contractorOptions} label={trans('messages.contractors.title')}/>
                                        <Form.Input required name="email" error={(errors && errors.email)?{content:errors.email[0],pointing:'under'}:null} onChange={this.handleCreateFormFieldChange} label="Отправить на почту"/>

                                        <Form.Checkbox style={{marginTop: 26 + 'px'}} name="no_nds" label="Без НДС" onChange={this.handleCreateFormFieldChange} value="1" checked={this.state.is_nonds} />
                                        <Form.Checkbox style={{marginTop: 26 + 'px'}} name="sf" label="Нужна счет-фактура" onChange={this.handleCreateFormFieldChange} value="1" checked={this.state.sf} />
                                    </Form.Group>
                                </Form>

                                <input type="hidden" name="upd" value="off"/>
                                {/*<input type="hidden" name="sf" value="off"/>*/}
                                {/*<Divider/>
                                <Form>

                                    <Form.Checkbox name="upd" onChange={this.handleCreateFormFieldChange} label={trans('messages.cheques.upd')}/>
                                    <Form.Checkbox name="sf" onChange={this.handleCreateFormFieldChange} label={trans('messages.cheques.sf')}/>
                                </Form>*/}

                                {error?<Message negative>
                                    <Icon name="warning sign"/> {trans('messages.organisations.limit_reached')}
                                </Message>:null}
                            </Modal.Content>
                            <Modal.Actions>
                                <Button negative onClick={this.closeCreateForm} style={{"display": this.state.confirm_active && totalAmount > 0 ? 'none' : 'inline-block'}}>
                                    <Icon name='x' /> {trans('messages.cancel')}
                                </Button>
                                {
                                    (this.state.newItem && this.state.newItem.data && this.state.newItem.data.positions && this.state.newItem.data.positions.length)
                                    ?<Button style={{"display": this.state.confirm_active ? 'none' : 'inline-block'}} positive onClick={this.askConfirm} disabled={ ((window.auth.id == 29) && (totalAmount>100000)) }><Icon name='checkmark' />  {trans('messages.save')}</Button>
                                    :null
                                }

                                <Message warning style={{"text-align": "center", "display": (this.state.confirm_active && totalAmount > 0) ? 'block' : 'none'}}>
                                    <Icon name="warning sign"/> С Вашего счета будет списано <Amount value={totalAmount > 0 ? (totalAmount * window.procent) : 0 }/> руб.
                                </Message>

                                <Button.Group style={{ "justify-content": "center", "display": this.state.confirm_active && totalAmount > 0 ? 'flex' : 'none'}}>
                                    <Button onClick={this.closeCreateForm} style={{ "flex": "0 0 auto", "width": "auto" }}>Отмена</Button>
                                        <Button.Or />
                                    <Button style={{ "flex": "0 0 auto", "width": "auto" }} positive onClick={this.addItem} ><Icon name='checkmark' />  {trans('messages.save')}</Button>
                                </Button.Group>
                            </Modal.Actions>
                        </Modal>
                    </Menu.Item>
                </Menu.Menu>
            </Menu>
            {/*<Grid centered>
                <Grid.Row columns={2}>
                    <Grid.Column width={6}>
                        {cheques.fetching
                            ?<Dimmer active><Loader/></Dimmer>
                            :<Statistic color="grey">
                                <Statistic.Value><Icon name="money bill alternate outline"/>{numeral(cheques.data.filter((item)=>{
                                        return ((new Date(item.created_at)).getTime() > monthFD.getTime());
                                    }).length).format('0,0')}</Statistic.Value>
                                <Statistic.Label>{trans('messages.cheques.month_count')}</Statistic.Label>
                            </Statistic>
                        }
                    </Grid.Column>
                    <Grid.Column width={10}>
                        {cheques.fetching
                            ?<Dimmer active><Loader/></Dimmer>
                            :<Statistic color="blue">
                                <Statistic.Value>{numeral(cheques.data.filter((item)=>{return (item.deleted_at == null && (new Date(item.created_at)).getTime() > monthFD.getTime());}).reduce( (accumulator,item) => {
                                        let sum = 0;
                                        if(item.data && item.data.positions)item.data.positions.map( (position,i) => {
                                            sum+=position.amount*position.quantity;
                                        })
                                        accumulator += sum;
                                        return accumulator;
                                    },0)).format('0,0.00') } <Icon name="ruble"/></Statistic.Value>
                                <Statistic.Label>{trans('messages.cheques.month_amount')}</Statistic.Label>
                            </Statistic>
                        }
                    </Grid.Column>
                </Grid.Row>
            </Grid>
            <Divider/>*/}
            <Form>
                <Form.Group>
                    <Form.Input placeholder={trans('messages.search')} width={8} icon="search" onChange={this.handleSearch} name="search" defaultValue={(search.search)?search.search:''}/>
                    {auth.can.admin?<Form.Select multiple onChange={this.handleSearch} options={userOptions} name="user_id" placeholder={trans('messages.users.title')}/>:null}
                    {auth.can.admin?<Form.Select multiple onChange={this.handleSearch} options={organisationSearchOptions} name="organisation_id" placeholder={trans('messages.organisations.title')}/>:null}
                </Form.Group>
                <Form.Group>
                    <SemanticDatepicker
                            format='DD.MM.YYYY'
                            width={8}
                            locale={ptLocale}
                            onDateChange={this.handleSearchDate}
                            type="range"/>
                    {auth.can.admin ? <Form.Select multiple onChange={this.handleSearch} options={chequeTypesOptions} name="type" placeholder="Тип"/> : null}
                    {auth.can.admin ? <Form.Select multiple onChange={this.handleSearch} options={chequeStatusesOptions} name="status" placeholder="Статус"/> : null}

                </Form.Group>
            </Form>
            <Divider horizontal>{trans('messages.list')}</Divider>

            {(selected_cheques_cnt>0)?<div style={{"display":"flex","gap":"10px"}}><Form><FormField inline><label>Выбрано: { selected_cheques_cnt }</label><Button color='red' size='tiny' onClick={this.clearSelected}>х</Button></FormField></Form><Form><FormField inline><Button color='green' size='tiny' onClick={this.printSelected} disabled={printing_many}>{printing_many ? 'Печать...' : 'Печать выбранных'}</Button></FormField></Form></div>:null}
            {
                (cheques.fetching)
                ?<Dimmer active inverted><Loader/></Dimmer>
                :<Table celled selectable>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell><Form.Checkbox onChange={this.updateSelectAll} cheques={pagination.data.map( (item,i) => { return item.id; })} checked={select_all_checked}/></Table.HeaderCell>
                            <Table.HeaderCell>№</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.cheques.created_at')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.cheques.organisation')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.cheques.amount')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.cheques.nds')}</Table.HeaderCell>
                            <Table.HeaderCell>Тип</Table.HeaderCell>
                            <Table.HeaderCell>Email</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.cheques.status')}</Table.HeaderCell>
                            <Table.HeaderCell><Button icon="refresh" color="green" onClick={this.handleSearch} ref="fresh" data-tooltip="Обновить статус" data-position="left center"></Button></Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {pagination.data.map( (item,i) => { return (<Cheque key={`cheque_${item.id}`} item={item} organisations={organisations} nomenclatures={nomenclatures} handleUpdate={this.updateItem} handleUpdateSelected={this.updateSelected} selected_cheques={selected_cheques} refresh={refresh}/>); } )}
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
            contractors:state.contractors,
            cheques:state.cheques,
            organisations:state.organisations,
            categories:state.categories,
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
)(Cheques);
