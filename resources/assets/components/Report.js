import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Button, Divider,Dimmer,Loader, Icon, Header, Table, Label } from 'semantic-ui-react';

import {Amount, Date} from './simple';
import {trans} from '../constants';

export class Report extends Component {
    constructor(props){
        super(props);
    }
    aggregate(){
        let report = [];
        const {users,cheques,contractors} = this.props;
        const findByUser = (user,contractor) => {
            if(!user) return null;
            for(let i in report){
                const row = report[i];
                if(row.user.id == user.id && (row.contractor!=null && contractor != null && row.contractor.id == contractor.id)) return row;
            }
            const newRow =  {
                user:user,
                contractor: contractor,
                amount:0,
                lean:0
            }
            report.push(newRow);
            return newRow;
        }
        const getUser = (id) => {
            if( !users && !users.data) return null;
            for(let i in users.data){
                const row = users.data[i];
                if(row.id == id) return row;
            }
            return null;
        }
        const getAmount = (cheque) => {
            let amount = 0;
            if( !(cheque && cheque.data && cheque.data.positions) )return amount;
            cheque.data.positions.map( (item) => {
                amount+= parseFloat(item.amount);
            } )
            return amount;
        }
        const getContractor = (cheque) => {
            if( !(cheque && cheque.data && cheque.contractor) )return null;
            const contractor_id = cheque.data.contractor.id;
            for(let i in contractors.data){
                const contractor = contractors.data[i];
                if (contractor.id == contractor_id) return contractor;
            }
            return null;
        }
        cheques.data.map( (item,i) => {
            const user = getUser( item.user_id );
            const contractor = getContractor ( item);
            const row = findByUser( user, contractor );
            if( row ){
                const amount = getAmount ( item );
                row.contractor = contractor;
                row.amount+= amount;
                const percent = amount * (contractor)?(contractor.percent/100):0;
                row.lean+= percent;
            }

        });
        return report;
    }
    render() {
        const report = this.aggregate();

        return <div><Header>{trans('messages.reports.title')}</Header><Table celled selectable>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell>{trans('messages.users.title')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.contractors.title')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.reports.total_amount')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.reports.total_lean')}</Table.HeaderCell>
                            <Table.HeaderCell>{trans('messages.reports.bad_dept')}</Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {report.map( (item,i) => {
                            return <Table.Row key={i}>
                                <Table.Cell>{item.user.name}</Table.Cell>
                                <Table.Cell>{(item.contractor)?item.contractor.name:''}</Table.Cell>
                                <Table.Cell><Amount value={item.amount}/></Table.Cell>
                                <Table.Cell><Amount value={item.lean}/></Table.Cell>
                                <Table.Cell><Amount value={item.user.bad_dept}/></Table.Cell>
                            </Table.Row>
                        }) }
                    </Table.Body>
                    <Table.Footer></Table.Footer>
                </Table></div>
    }
}

/*
<Table.Cell>{item.id}</Table.Cell>
<Table.Cell><Date value={item.created_at}/></Table.Cell>
<Table.Cell>{item.organisation ?item.organisation.name :''}</Table.Cell>
<Table.Cell textAlign="right"><Amount value={sums.amount}/></Table.Cell>
<Table.Cell textAlign="right"><Amount value={sums.nds}/></Table.Cell>
<Table.Cell>{trans(`messages.cheques.statuses.${item.status}`)}</Table.Cell>
<Table.Cell>
    <Button color="blue" icon onClick={this.viewCheque}>
        <Icon name="eye"/>
    </Button>
    <Modal closeIcon={<Icon name="close" onClick={this.viewCheque}/>} open={viewing} centered={false}>
        <Header align="center"># {item.id}</Header>
        <Modal.Content className="cheque">
            <Header align="center"><Icon name="calendar"/> {item.created_at}</Header>
            {organisation?<Header align="center">
                <Header.Content>{organisation.name}</Header.Content>
                <Header.Subheader>{trans('messages.organisations.inn')}: <b>{organisation.data.inn}</b>, {trans('messages.organisations.kpp')}: <b>{organisation.data.kpp}</b>,  {organisation.data.address}</Header.Subheader>
            </Header>:null}
            <Table collapsing compact size="small" align="center">
                <Table.Body>
                    {item.data&&item.data.positions?item.data.positions.map( (position,i) => {
                        return (<Table.Row key={i}>
                            <Table.Cell width={12}>{position.nomenclature || position.name}({trans('messages.nds')} {nds*100}%)</Table.Cell>
                            <Table.Cell width={4} textAlign="right">{position.quantity}x{position.amount}<Icon name="ruble"/><br/>=<strong>{position.amount*position.quantity}<Icon name="ruble"/></strong></Table.Cell>
                        </Table.Row>);
                    }):null}
                </Table.Body>
                {(sums.quantity>0)?<Table.Footer>
                    <Table.Row>
                        <Table.Cell width={8} textAlign="right">{trans('messages.totals.amount')}</Table.Cell>
                        <Table.HeaderCell width={8} textAlign="right">{sums.amount}<Icon name="ruble"/></Table.HeaderCell>
                    </Table.Row>
                    <Table.Row>
                        <Table.Cell width={8} textAlign="right">{trans('messages.totals.nds')}</Table.Cell>
                        <Table.HeaderCell width={8} textAlign="right">{sums.nds}<Icon name="ruble"/></Table.HeaderCell>
                    </Table.Row>
                </Table.Footer>:null}
            </Table>
            {contractor?<Header align="center">
                <Header.Content>{contractor.name}</Header.Content>
                <Header.Subheader>{trans('messages.organisations.inn')}: <b>{contractor.inn}</b>, {trans('messages.organisations.kpp')}: <b>{contractor.kpp}</b>,  {contractor.address}</Header.Subheader>
            </Header>:null}

        </Modal.Content>
    </Modal>
</Table.Cell>

 */
