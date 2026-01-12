import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Container, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header, Table, Label, Modal, Checkbox } from 'semantic-ui-react';
import { Link } from 'react-router-dom';

import QRCode from 'react-qr-code';

import {Amount, Date, DateTime} from './simple';
import {modelRest} from '../actions';
import {trans,USER_ROLES,REMOVE,UPDATE} from '../constants';
import ReactDOM from 'react-dom'

class Cheque extends Component {

    constructor(props){
        super(props);
        this.state={
            item: this.props.item,
            errors: false,
            viewing: false,
            printing: false,
            printing_1: false,
            printing_2: false,
            printers_visible: false,
            selected_cheques_inner: [],
        }
    }

    handleFieldChange = (e, {name,value}) => {
        let {item} = this.state;
        item[name] = value;
        this.setState({item:item});
    }
    printCheque = () => {
        document.getElementById("chequeFrame").contentWindow.print();
    }
    printOnlineCheque = () => {
        this.setState({printing: true});

        const url = "/cheque/" + this.state.item.id + "/print";

        const requestMetadata = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.getElementsByTagName("meta")['csrf-token'].content
            },
            body: JSON.stringify({})
        };

        fetch(url, requestMetadata)
            .then(res => res.json())
            .then(result => {
                this.setState({printing: false});
            });
    }
    printOnlineChequeToQueue = (queue_id) => {
        this.setState({ printers_visible: false })

        if (queue_id == 1) { this.setState({printing_1: true}); }
        if (queue_id == 2) { this.setState({printing_2: true}); }
        
        const url = "/cheque/" + this.state.item.id + "/print_queue/" + queue_id;

        const requestMetadata = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.getElementsByTagName("meta")['csrf-token'].content
            },
            body: JSON.stringify({})
        };

        fetch(url, requestMetadata)
            .then(res => res.json())
            .then(result => {
                if (queue_id == 1) { this.setState({printing_1: false}); }
                if (queue_id == 2) { this.setState({printing_2: false}); }
            });
    }
    viewCheque = () => {
        this.setState({ printers_visible: false });

        this.setState({ viewing:!this.state.viewing });
    }

    reprintCheque = (e) => {
        const {handleUpdate} = this.props;
        const {item} = this.state;
        item.status = "new";

        handleUpdate(e, item);

        this.setState({item: item});
    }

    handleChequeChange = (e, {name, value}) => {
        console.log('handleChequeChange');
        const {handleUpdateSelected} = this.props;

        const index = window.selected_cheques.indexOf(value);
        if (index > -1) { // only splice array when item is found
            window.selected_cheques.splice(index, 1); // 2nd parameter means remove one item only
        } else {
            window.selected_cheques.push(value);
        }

        handleUpdateSelected(window.selected_cheques);

        this.setState({selected_cheques_inner: window.selected_cheques});
    }

    render() {
        const {organisations} = this.props;
        const {selected_cheques} = this.props;
        const {selected_cheques_inner} = this.state;
        const {item,viewing} = this.state;
        const {editing,errors} = this.state;
        const {printing} = this.state;
        const {printing_1} = this.state;
        const {printing_2} = this.state;
        const {printers_visible} = this.state;
        const {refresh} = this.state;
        const {organisation} = item;
        const {contractor} = item.data;
        const handleEditing = (e)=>{
            this.setState({editing:!this.state.editing});
        }

        const organisationOptions = organisations.data.map( (item,i) => {
            return {
                value: item.id,
                text: item.name
            }
        })
        let nds = 0; //(organisation && organisation.data.nds)?organisation.data.nds:0;
        let sums = {
            amount:0,
            nds:0,
            quantity:0,
            positions:item.data.length
        }
        if(item.data.positions) item.data.positions.map( (item) => {
            nds = item.nds;
            sums.amount+= parseFloat(item.amount)*parseFloat(item.quantity);
            sums.nds += calculateNds(parseFloat(item.amount)*parseFloat(item.quantity), nds*100);
            sums.quantity += parseInt(item.quantity);
        });

        const rowStatusError = (item.status == window.cheques.statuses.failed);
        const rowStatusWarn = (item.status == window.cheques.statuses.inprogress);
        const rowStatusSucces = (item.status == window.cheques.statuses.success);

        if (item.status_text) {
            status = item.status_text;
        } else {
            status = trans(`messages.cheques.statuses.${item.status}`);
        }

        return <Table.Row warning={rowStatusWarn} negative={rowStatusError} positive={rowStatusSucces}>
                    <Table.Cell><Form.Checkbox name="to_print" value={item.id} onChange={this.handleChequeChange} checked={(selected_cheques.indexOf(item.id) != -1)} />{ refresh }</Table.Cell>
                    <Table.Cell>{item.id}</Table.Cell>
                    <Table.Cell><DateTime value={item.created_at}/></Table.Cell>
                    <Table.Cell>{item.organisation ? item.organisation.name : ''}</Table.Cell>
                    <Table.Cell textAlign="right"><Amount value={sums.amount}/></Table.Cell>
                    <Table.Cell textAlign="right"><Amount value={sums.nds}/></Table.Cell>
                    <Table.Cell>{window.cheques.types[item.type]}</Table.Cell>
                    <Table.Cell>{item.email ? item.email : '-'}</Table.Cell>
                    <Table.Cell>{status}</Table.Cell>
                    <Table.Cell>
                        <Button disabled={item.status != 'success'} color="blue" icon onClick={this.viewCheque} data-tooltip="Просмотреть чек" data-position="left center">
                            <Icon name="eye"/>
                        </Button>
                        {
                            (item.status == 'success')
                                ? null
                                : (<Button color="grey" icon onClick={this.reprintCheque} title="Перепечатать" data-tooltip="Перепечатать чек" data-position="left center"><Icon name="refresh"/></Button>)
                        }
                        {
                            item.file ? (<a download={`${item.file}.pdf`} href={`/cheques/${item.file}.pdf`}>
                                <Icon name="download"/>
                            </a>) : null
                        }
                        {
                            (false && (item.status == 'success')) ? (<Button color="black" icon onClick={this.exportCheque} title="Отправить чек в магазин" data-tooltip="Отправить чек в магазин" data-position="left center"><Icon name="shopping bag"/></Button>) : null
                        }
                        {
                            (false && (item.status == 'success') && (item.export == 0)) ? (<Button color="black" icon onClick={this.exportCheque} title="Отправить чек в магазин" data-tooltip="Отправить чек в магазин" data-position="left center"><Icon name="shopping bag"/></Button>) : null
                        }
                        {
                            (false && (item.status == 'success') && (item.export == 1)) ? (<Button color="black" icon onClick={this.exportCheque} disabled><Icon name="shopping bag"/></Button>) : null
                        }
                        <Modal closeIcon={<Icon name="close" onClick={this.viewCheque}/>} open={viewing} centered={false}>
                            <Header align="center" style={{ position: 'relative'}}>
                                # {item.doc_num}
                                <div style={{ position: 'absolute', top: 10, right: 10 }}>
                                    {/*<Link to={`/cheque/view/${item.id}?pdf`} target="_blank" download={`${item.id}.pdf`}>
                                        <Button>Скачать</Button>
                                    </Link>*/}
                                    <Link to={`/cheque/view_pdf/${item.id}?pdf`} target="_blank" download={`${item.id}.pdf`}>
                                        <Button>Скачать (v2)</Button>
                                    </Link>
                                    {/*<Button onClick={this.printCheque}>Печать</Button>*/}
                                    {/*<Button onClick={this.printOnlineCheque}>{ printing ? 'Подождите...' : 'Печать' }</Button>*/}
                                    {/*<Button onClick={() => this.printOnlineChequeToQueue(1)}>{ printing_1 ? 'Подождите...' : 'Печать' }</Button>*/}
                                    {/*<Button onClick={() => this.printOnlineChequeToQueue(2)}>{ printing_2 ? 'Подождите...' : 'Печать2' }</Button>*/}

                                    <div style={{ display: 'inline-block' }}>  
                                        <Button onClick={() => this.setState({ printers_visible: !printers_visible })}>{ printing_1 ? 'Подождите...' : 'Печать' }</Button>
                                        <div class="ui vertical buttons" style={{ position: 'absolute', right: '3px', top: 'calc(100% + 4px)', display: printers_visible ? 'block' : 'none' }}>
                                            <Button onClick={() => this.printOnlineChequeToQueue(1)}>{ printing_1 ? 'Подождите...' : 'Принтер 1' }</Button>
                                            <Button onClick={() => this.printOnlineChequeToQueue(2)}>{ printing_2 ? 'Подождите...' : 'Принтер 2' }</Button>
                                        </div>
                                    </div>
                                </div>
                            </Header>
                            <Modal.Content className="cheque">
                                <iframe id="chequeFrame" src={`/cheque/view/${item.id}`} style={{ display: 'block', margin: '0 auto', width: '480px', border: '1px solid #ccc', height: '0px' }}></iframe>
                            </Modal.Content>
                        </Modal>
                    </Table.Cell>
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
)(Cheque);
