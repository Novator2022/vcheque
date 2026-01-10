import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Support from '../components/Support';

class Supports extends Component {
    constructor(props){
        super(props);
        this.state = {
            search:{}
        };
    }
    componentDidMount(){
        this.props.action('support', 'LIST');
        this.props.action('user', 'LIST');
    }
    handleSearch = (e,{name,value})=>{
        let {search} = this.state;
        search[name] = value;
        if(e.target.type=="checkbox")search[name] = e.target.checked?"on":"off";
        this.setState({search: search},
            ()=>{
                this.props.action(
                    'support',
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
        const {supports,expensetypes,nomenclatures,organisations,users} = this.props;
        const {search} = this.state;
        const userOptions = [{
            value: "",
            text: trans('messages.all')
        }];
        if(!users.fetching)users.data.map( (item,i) => {
            userOptions.push({
                value: item.id,
                text: item.name
            })
        });
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{trans('messages.supports.title')}</Header></Menu.Item>
            </Menu>
            <Form>
                <Form.Group>
                    <Form.Input placeholder={trans('messages.search')} width={8} icon="search" onChange={this.handleSearch} name="search" defaultValue={(search.search)?search.search:''}/>
                    <Form.Select multiple placeholder={trans('messages.users.title')} loading={users.fetching} required name="user_id" options={userOptions} value={(search.user_id)?search.user_id:[]} onChange={this.handleSearch}/>
                </Form.Group>
            </Form>
            <Divider horizontal>{trans('messages.list')}</Divider>
            <Item.Group divided>
            {
                supports.fetching
                ?(<Dimmer active inverted><Loader /></Dimmer>)
                :(supports.data.map((item,i)=>{return (<Support key={i} item={item}  organisations={organisations} nomenclatures={nomenclatures} expensetypes={expensetypes}/>); }))
            }
            </Item.Group>
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            supports:state.supports,
            nomenclatures:state.nomenclatures,
            organisations:state.organisations,
            expensetypes:state.expensetypes,
            users:state.users
        };
    },
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(Supports);
