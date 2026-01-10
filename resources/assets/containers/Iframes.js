import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Table, Pagination } from 'semantic-ui-react';

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Log from '../components/Log';
import {paginate} from '../helpers/paginator';

class Iframes extends Component {
    constructor(props) {
        super(props);
        this.state = {
            src: props.source,
            heading: props.heading
        };
    }
    
    render() {
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item><Header className="first on top">{this.state.heading}</Header></Menu.Item>
            </Menu>
            
            <iframe id="iframe" style={{ width: "100%", hiehgt: 0, border: "0" }} class="iframe" frameborder='0' scrolling='no' src={this.state.src} onLoad={() => {
                document.getElementById('iframe').style.height = document.getElementById('iframe').contentWindow.document.documentElement.scrollHeight + 'px';
            }}></iframe>
        </Segment>
    }
}
export default connect(
    (state,ownProps)=>{
        return {
            logs:state.logs,
            nomenclatures:state.nomenclatures,
            contractors:state.contractors,
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
)(Iframes);
