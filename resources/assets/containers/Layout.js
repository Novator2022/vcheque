import React, { Component } from 'react'
import PropTypes from 'prop-types';
import { BrowserRouter, Route, Link } from "react-router-dom";
import { connect } from 'react-redux';
import { Sidebar, Container, Segment, Button, Menu, Image, Icon, Header,Statistic,Divider, Modal, Form } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans,UPDATE,CREATE} from '../constants';
import {Footer} from '../components/Footer';
import Users from './Users';
import Organisations from './Organisations';
import ExpenseTypes from './ExpenseTypes';
import Nomenclatures from './Nomenclatures';
import Categories from './Categories';
import Events from './Events';
import Cheques from './Cheques';
import ChequeSchedules from './ChequeSchedules';
import Logs from './Logs';
import Iframes from './Iframes';
import Supports from './Supports';
import DashboardAdmin from './DashboardAdmin';
import DashboardUser from './DashboardUser';
import Contractors from './Contractors';

class Layout extends Component {
    constructor(props){
        super(props);
        const {auth} = window;
        this.state = {
            supportModal: false,
            passwordModal: false,
            visible: (auth.role!='user'),
            activeItem: document.location.href.replace(/.+\/(.+)$/im,"$1"),
            message:'',
            update:{
                email: auth.email,
                phone: auth.phone
            }
        };
    }
    handleSupportMessageChange = (e,{value}) => { this.setState({message:value})}
    addSupport = ()=>{
        this.props.action(
            'support',
            CREATE,
            {user_id:window.auth.id,message:this.state.message},
            (response)=>{
                this.setState({supportModal:false});
            },
            (error)=>{
                this.setState({
                    errors:error.response.data
                })
            }
        );
    }
    handleUpdateForm = (e,{name,value}) => {
        const {update} = this.state;
        update[name]=value;
        this.setState({update:update})
    }
    changePassword = () =>{
        const {update} = this.state;
        const {auth} = window;
        const that = this;
        this.props.action('user',UPDATE,{
            id: auth.id,
            email: update.email,
            phone: update.phone,
            password: update.password
        },(d) => {

            that.setState({update:{
                email: d.email,
                phone: d.phone
            },passwordModal: false})
        });

    }
    render() {
        const {auth} = window;
        const toggleVisibility = () => { this.setState({ visible: !this.state.visible }) };
        const handleItemClick = (name) => { this.setState({ activeItem: name,visible:true }) }
        const { visible, activeItem, supportModal,passwordModal } = this.state;
        const HomePage = () => { return auth.can.manager?(<DashboardAdmin/>):(<Cheques/>);};
        const UsersPage = () => {return (<Users/>)}
        const OrganisationsPage = () => { return (<Organisations/>) }
        const ExpenseTypesPage = () => { return (<ExpenseTypes/>) }
        const NomenclaturesPage = () => { return (<Nomenclatures/>) }
        const CategoriesPage = () => { return (<Categories/>) }
        const ChequesPage = () => { return (<Cheques/>) }
        const ChequeSchedulesPage = () => { return (<ChequeSchedules/>) }
        const LogsPage = () => { return (<Logs/>) }
        const TariffPage = () => { return (<Iframes source='/home/tariff/frame' heading='Тариф' />) }
        const BalancePage = () => { return (<Iframes source='/home/balance/frame' heading='Баланс' />) }
        const SupportsPage = () => { return (<Supports/>) }
        const ContractorsPage = () => { return (<Contractors/>) }
        const openSupport = ()=>{this.setState({supportModal:true})}
        const closeSupport = ()=>{this.setState({supportModal:false})}
        const closePassword = ()=>{this.setState({passwordModal:false})}
        return (
            <BrowserRouter>
                <Menu inverted>
                    <Menu.Item name='sidebar' onClick={ toggleVisibility }><Icon name="bars"/></Menu.Item>
                    {auth.role == 'user'?<Menu.Item name='home' as={Link} to="/home"><Icon name="home"/></Menu.Item>:null}
                    <Menu.Menu position="right">
                        {
                            auth.role == 'user'
                                ?<Menu.Item key={`support_menu_item_key`} name="support" onClick={()=>{this.setState({supportModal: true})}}><Icon name="help"/></Menu.Item>
                                :null
                            }
                        <Menu.Item key="events" name="events">
                            <Events/>
                        </Menu.Item>

                        <Menu.Item name="user"  name="changePassword">

                            <Button icon basic inverted onClick={()=>{this.setState({passwordModal:true})}}>
                                <Icon name="user"/>
                                { window.auth?window.auth.name:'' }
                            </Button>
                            <Modal closeIcon={<Icon name="close" onClick={()=>{this.setState({passwordModal:false})}}/>}  key={`password_window_key`} open={passwordModal}>
                                <Header icon='lock' content={trans('messages.change_password')} />
                                <Modal.Content>
                                    <Form>
                                        <Form.Input icon="mail" name="email" label={trans('messages.users.email')} defaultValue={auth.email} onChange={ this.handleUpdateForm }/>
                                        <Form.Input icon="phone" name="phone" label={trans('messages.users.phone')} defaultValue={auth.phone} onChange={ this.handleUpdateForm }/>
                                        <Form.Input icon="lock" name="password" label={trans('messages.new_password')} onChange={ this.handleUpdateForm }/>

                                    </Form>
                                </Modal.Content>
                                <Modal.Actions>
                                    <Button negative onClick={closePassword}>
                                        <Icon name='x' /> {trans('messages.cancel')}
                                    </Button>
                                    <Button positive onClick={this.changePassword}>
                                        <Icon name='checkmark' />  {trans('messages.send')}
                                    </Button>
                                </Modal.Actions>
                            </Modal>
                        </Menu.Item>
                        <Menu.Item name="logout">
                            <form action="/logout" method="POST" id="logout-form">
                                <input type="hidden" name="_token" value={window.csfrToken}/>
                                <Button size="mini" basic inverted type="submit" icon><Icon name="sign out"/></Button>
                            </form>
                        </Menu.Item>
                    </Menu.Menu>
                </Menu>
                <Sidebar.Pushable as={Segment} className="first on top">
                    <Sidebar as={Menu} animation='overlay' width='thin' visible={visible} icon='labeled' vertical inverted>
                        <Menu.Item as={Link} to="/home" name='home' active={activeItem ==='home'} onClick={(e,d)=>handleItemClick(d.name)}><Icon name='home' />{trans('messages.pages.main')}</Menu.Item>
                        {auth.can.admin?<Menu.Item as={Link} to="/home/users" name='users' active={activeItem==='users'} onClick={(e,d)=>handleItemClick(d.name)}><Icon name='users' />{trans('messages.users.title')}</Menu.Item>:null}
                        {auth.can.user?<Menu.Item as={Link} to="/home/contractors" name='contractors' active={activeItem==='contractors'} onClick={handleItemClick}><Icon.Group size="big"><Icon name='building' /><Icon corner="top left" color="grey" name='user' /></Icon.Group><br/>{trans('messages.contractors.title')}</Menu.Item>:null}
                        {auth.can.admin?<Menu.Item as={Link} to="/home/expensetypes" name='expensetypes' active={activeItem==='expensetypes'} onClick={handleItemClick}><Icon name='code branch' />{trans('messages.expensetypes.title')}</Menu.Item>:null}
                        {auth.can.admin?<Menu.Item as={Link} to="/home/organisations" name='organisations' active={activeItem==='organisations'} onClick={handleItemClick}><Icon name='building' />{trans('messages.organisations.title')}</Menu.Item>:null}
                        {auth.can.admin?<Menu.Item as={Link} to="/home/nomenclatures" name='nomenclature' active={activeItem==='nomenclatures'} onClick={handleItemClick}><Icon name='cart' />{trans('messages.nomenclatures.title')}</Menu.Item>:null}
                        {auth.can.admin?<Menu.Item as={Link} to="/home/categories" name='category' active={activeItem==='categories'} onClick={handleItemClick}><Icon name='sitemap' />{trans('messages.categories.title')}</Menu.Item>:null}
                        {auth.can.user?<Menu.Item as={Link} to="/home/cheques" name='cheques' active={activeItem==='cheques'} onClick={handleItemClick}><Icon name='money bill alternate outline' />{trans('messages.cheques.title')}</Menu.Item>:null}
                        {auth.can.user?<Menu.Item as={Link} to="/home/cheque_schedules" name='cheque_schedules' active={activeItem==='cheque_schedules'} onClick={handleItemClick}><Icon name='money' />Чеки по расписанию</Menu.Item>:null}
                        {auth.can.user?<Menu.Item as={Link} to="/home/logs" name='logs' active={activeItem==='logs'} onClick={handleItemClick}><Icon name='file text' />{trans('messages.logs.title')}</Menu.Item>:null}
                        {auth.can.user?<Menu.Item as={Link} to="/home/tariff" name='tariff' active={activeItem==='tariff'}><Icon name='file text' />Тариф</Menu.Item>:null}
                        {auth.can.user?<Menu.Item as={Link} to="/home/balance" name='balance' active={activeItem==='balance'}><Icon name='money bill alternate' />Баланс</Menu.Item>:null}
                        {auth.can.admin?<Menu.Item><Divider/></Menu.Item>:null}
                        {auth.can.admin?<Menu.Item as={Link} to="/home/supports" name='supports'  active={activeItem==='supports'} onClick={handleItemClick}><Icon name='help' />{trans('messages.supports.title')}</Menu.Item>:null}
                    </Sidebar>
                    <Sidebar.Pusher className="content wrapper">
                            <Route exact path="/home" component={HomePage} />
                            <Route path="/home/users" component={UsersPage} />
                            <Route path="/home/contractors" component={ContractorsPage} />
                            <Route path="/home/expensetypes" component={ExpenseTypesPage} />
                            <Route path="/home/organisations" component={OrganisationsPage} />
                            <Route path="/home/nomenclatures" component={NomenclaturesPage} />
                            <Route path="/home/categories" component={CategoriesPage} />
                            <Route path="/home/cheques" component={ChequesPage} />
                            <Route path="/home/cheque_schedules" component={ChequeSchedulesPage} />
                            <Route path="/home/logs" component={LogsPage} />
                            <Route path="/home/tariff" component={TariffPage} />
                            <Route path="/home/balance" component={BalancePage} />
                            <Route path="/home/supports" component={SupportsPage} />
                    </Sidebar.Pusher>
                </Sidebar.Pushable>
                <Footer/>

                    <Modal key={`support_window_key`} open={supportModal}  closeIcon={<Icon name="close" onClick={()=>{this.setState({supportModal:false})}}/>}>
                        <Header icon='wheelchair' content={trans('messages.supports.add')} />
                        <Modal.Content>
                            <Form>
                                <Form.TextArea required name="message" label={trans('messages.supports.message')} onChange={ this.handleSupportMessageChange }/>
                            </Form>
                        </Modal.Content>
                        <Modal.Actions>
                            <Button negative onClick={closeSupport}>
                                <Icon name='x' /> {trans('messages.cancel')}
                            </Button>
                            <Button positive onClick={this.addSupport}>
                                <Icon name='checkmark' />  {trans('messages.send')}
                            </Button>
                        </Modal.Actions>
                    </Modal>
            </BrowserRouter>
        );
    }
}
export default connect(
    null,
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(Layout);
