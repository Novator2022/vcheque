import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Grid, Message } from 'semantic-ui-react';
import numeral from 'numeral';


import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Organisation from '../components/Organisation';

class DashboardUser extends Component {
    constructor(props){
        super(props);
        this.state = {
            search:{}
        };
    }
    componentDidMount(){
        this.props.action('cheque', 'LIST');
        this.props.action('nomenclature', 'LIST');
        this.props.action('organisation', 'LIST');
    }
    handleSearch = (e,{name,value})=>{
        let {search} = this.state;
        search[name] = value;
        if(e.target.type=="checkbox")search[name] = e.target.checked?"on":"off";
        this.setState({search: search},
            ()=>{
                this.props.action(
                    'organisation',
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
                    'organisation',
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
        const {expensetypes,organisations,users,cheques} = this.props;
        const {search} = this.state;
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.dashboards.title')}</Header></Menu.Item>
                <Menu.Menu position="right">
                        <Menu.Item>
                            <Button icon primary onClick={()=>{this.setState({createFormOpen:true})}}><Icon name="plus"></Icon></Button>
                            <Modal open={createFormOpen} centered={false}>
                                <Header icon='plus' content={trans('messages.add')} />
                                <Modal.Content>
                                    <Form>
                                        <Form.Group>
                                            <Form.Select width={8} search required name="expense_type_id" error={(errors && errors.expense_type_id)?{content:errors.expense_type_id[0],pointing:'below'}:null} options={expensetypeOptions} value={newItem.expense_type_id} label={trans('messages.expensetypes.title')} onChange={this.handleCreateFormFieldChange}/>
                                            <Form.Select width={8} search key={'cheque_organisation_id'} required name="organisation_id" error={(errors && errors.organisation_id)?{content:errors.organisation_id[0],pointing:'below'}:null} options={organisationOptions}  label={trans('messages.organisations.title')} onChange={this.handleCreateFormFieldChange}/>
                                        </Form.Group>
                                        <Form.Group>
                                            <Form.Select width={8} search key={'cheque_nomenclature_id'} required name="nomenclature_id" value={this.state.position.nomenclature_id} error={(errors && errors.nomenclature_id)?{content:errors.nomenclature_id[0],pointing:'below'}:null} options={nomenclatureOptions} label={trans('messages.nomenclatures.title')} onChange={this.handlePositionFieldChange}/>
                                            <Form.Input width={4} type="number" step={1} name="amount" defaultValue={this.state.position.amount} label={trans('messages.amount')} icon="ruble" onChange={this.handlePositionFieldChange}/>
                                            <Form.Input width={3} type="number" step={1} name="quantity" defaultValue={this.state.position.quantity} label={trans('messages.quantity')} onChange={this.handlePositionFieldChange}/>
                                            <Form.Button width={1} disabled={error} type="button" step={1} label="&nbsp;" onClick={this.handleAddPosition} color="orange" icon><Icon name="plus"/></Form.Button>
                                        </Form.Group>
                                    </Form>
                                    <Divider/>
                                    <Container className="cheque">
                                        {organisation?<Header align="center">
                                            <Header.Content>{organisation.name}</Header.Content>
                                            <Header.Subheader>{trans('messages.organisations.inn')}: <b>{organisation.data.inn}</b>, {trans('messages.organisations.kpp')}: <b>{organisation.data.kpp}</b>,  {organisation.data.address}</Header.Subheader>
                                        </Header>:null}
                                        <Table collapsing compact size="small" align="center">
                                            <Table.Body>
                                                {newItem.data&&newItem.data.positions?newItem.data.positions.map( (position,i) => {
                                                    const nds = parseInt(position.nds)/100;
                                                    totalAmount += position.amount*position.quantity;
                                                    totalNds += position.quantity*position.amount*nds;
                                                    totalQuantity += position.quantity;
                                                    error = (organisation!=undefined && organisation.limit<totalAmount) ;
                                                    return (<Table.Row key={i}>
                                                        <Table.Cell width={12}>{position.name}({trans('messages.nds')} {position.nds}%)</Table.Cell>
                                                        <Table.Cell width={4} textAlign="right">
                                                            {position.quantity}x{position.amount}<Icon name="ruble"/><br/>=<strong>{position.amount*position.quantity}<Icon name="ruble"/></strong>
                                                            <a href="#" onClick={()=>this.removePosition(i)}><Icon name="x"/></a>
                                                        </Table.Cell>
                                                    </Table.Row>);
                                                }):null}
                                            </Table.Body>
                                            {(totalQuantity>0)?<Table.Footer>
                                                <Table.Row>
                                                    <Table.Cell width={8} textAlign="right">{trans('messages.totals.amount')}</Table.Cell>
                                                    <Table.HeaderCell width={8} textAlign="right">{totalAmount}<Icon name="ruble"/></Table.HeaderCell>
                                                </Table.Row>
                                                <Table.Row>
                                                    <Table.Cell width={8} textAlign="right">{trans('messages.totals.nds')}</Table.Cell>
                                                    <Table.HeaderCell width={8} textAlign="right">{totalNds}<Icon name="ruble"/></Table.HeaderCell>
                                                </Table.Row>
                                            </Table.Footer>:null}
                                        </Table>
                                    </Container>
                                    {error?<Message negative>
                                        <Icon name="warning sign"/> {trans('messages.organisations.limit_reached')}
                                    </Message>:null}
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
            <Grid centered>
                <Grid.Row columns={2}>
                    <Grid.Column width={6}>
                        {cheques.fetching
                            ?<Dimmer active><Loader/></Dimmer>
                            :<Statistic color="grey">
                                <Statistic.Value><Icon name="money bill alternate outline"/>{numeral(cheques.data.filter((item)=>{
                                        return ((new Date(item.created_at)).getTime()>monthFirstDay().getTime());
                                    }).length).format('0,0')}</Statistic.Value>
                                <Statistic.Label>{trans('messages.cheques.month_count')}</Statistic.Label>
                            </Statistic>
                        }
                    </Grid.Column>
                    <Grid.Column width={10}>
                        {cheques.fetching
                            ?<Dimmer active><Loader/></Dimmer>
                            :<Statistic color="blue">
                                <Statistic.Value>{numeral(cheques.data.filter((item)=>{return (item.deleted_at == null && (new Date(item.created_at)).getTime()>monthFirstDay().getTime());}).reduce( (accumulator,item) => {
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
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            expensetypes:state.expensetypes,
            nomenclatures:state.nomenclatures,
            organisations:state.organisations,
            cheques:state.cheques,
        };
    },
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(DashboardUser);
