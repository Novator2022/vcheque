import React, { Component } from 'react'
import { connect } from 'react-redux';
import { Segment, Form, Button, Menu,Divider,Dimmer,Loader,Item, Image, Icon, Header,Statistic, Modal,Search, Grid } from 'semantic-ui-react';
import SemanticDatepicker from 'react-semantic-ui-datepickers';
import ptLocale from 'react-semantic-ui-datepickers/dist/locales/ru-RU';
import 'react-semantic-ui-datepickers/dist/react-semantic-ui-datepickers.css';
import numeral from 'numeral';

import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'

import {modelRest} from '../actions';
import {trans, USER_ROLES,CREATE,USER_ROLES_SEARCH,LIST} from '../constants';
import Organisation from '../components/Organisation';
import Organisations from './Organisations';
import {Report} from '../components/Report';

import {VDate} from '../helpers/dates';

numeral.register('locale', 'ru', {
    delimiters: {
        thousands: ' ',
        decimal: '.'
    },
    abbreviations: {
        thousand: 'к',
        million: 'млн',
        billion: 'млрд',
        trillion: 'трлн'
    },
    ordinal : function (number) {
        return number === 1 ? 'ч' : 'н';
    },
    currency: {
        symbol: 'Р'
    }
});
numeral.locale('ru');
class DashboardAdmin extends Component {
    constructor(props){
        super(props);
        const dates = VDate.currentMonth();

        console.log('1');
        console.log(dates);

        this.state = {
            search:{
                dates: [
                    VDate.truncDate(dates[0]),
                    VDate.truncDate(dates[1], true)
                ]
            }
        };
    }
    componentDidMount(){
        const {props} = this;
        const {search} = this.state;
        if (!props.users.data.length) this.props.action('user', 'LIST');
        if (!props.cheques.data.length) this.props.action('cheque', 'LIST', search);
        if (!props.contractors.data.length) this.props.action('contractor', 'LIST', search);
        if (!props.organisations.data.length) this.props.action('organisation', 'LIST');
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
    handleSearchDate = (dates)=>{
        let {search} = this.state;

        if (dates==null) {
            // search['dates'] = '';
            // this.setState({search: search},
            //     ()=>{
            //         this.props.action(
            //             'cheque',
            //             LIST,
            //             search,
            //             (response)=>{},
            //             (error)=>{}
            //         )
            //     }
            // );
        } else if (dates && dates.length>1) {
            if (typeof(dates[0]) == 'object') {
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
    }
    render() {
        const {expensetypes,organisations,users,cheques,contractors} = this.props;
        const {search} = this.state;
        const {dates} = search;

        const monthDays = () => {
            if( dates ){
                const firstDay  = VDate.fromEnDate(dates[0]);
                const finishDay = VDate.fromEnDate(dates[1]);

                const days = VDate.daysIn( firstDay, finishDay );
                return days;
            }
            else {
                const firstDay = monthFirstDay();
                const toDay = new Date();
                
                let ret = [firstDay];
                for(let i=2; i< toDay.getDate();++i){
                    ret.push(new Date(toDay.getFullYear(),toDay.getMonth(),i))
                }
                return ret;
            }
        }
        let chequesCountByDay = [];
        let chequesAmountByDay = [];
        let days = monthDays();

        // console.log(days);
        // console.log('imhere');

        for(let i in days ){
            const day = days[i];
            chequesAmountByDay.push( [
                day,
                cheques.data.filter((item)=>{return (item.deleted_at == null && (new Date(item.created_at)).getTime()%day.getTime() < 1000*24*60*60);}).reduce( (accumulator,item) => {
                        let sum = 0;
                        if(item.data && item.data.positions)item.data.positions.map( (position,i) => {
                            sum+=position.amount*position.quantity;
                        })
                        accumulator += sum;
                        return accumulator;
                    },0)
            ] );
            chequesCountByDay.push( [
                day,
                cheques.data.filter((item)=>{return (item.deleted_at == null && (new Date(item.created_at)).getTime()%day.getTime() < 1000*24*60*60);}).length
            ] );

        }
        const chartOptions = {
            title: {
                text: trans('messages.dashboards.chart_title')
            },

            subtitle: {
                text: trans('messages.dashboards.this_month')
            },
            yAxis: {
                type: 'datetime',
                dateTimeLabelFormats: { // don't display the dummy year
                    month: '%e. %b',
                    year: '%b'
                },
                title: {
                    text: trans('messages.day')
                }
            },
            yAxis: {
                title: {
                    text: 'Кол-во/Сумма'
                },
                min: 0
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
                pointFormat: '{point.x}: {point.y:.2f}'
            },

            plotOptions: {
                spline: {
                    marker: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            series: [{
                name: trans('messages.cheques.count'),
                data: chequesCountByDay
            }],
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        }
        const chartAmountOptions = {
            title: {
                text: trans('messages.dashboards.chart_title')
            },

            subtitle: {
                text: trans('messages.dashboards.this_month')
            },
            yAxis: {
                type: 'datetime',
                dateTimeLabelFormats: { // don't display the dummy year
                    month: '%e. %b',
                    year: '%b'
                },
                title: {
                    text: trans('messages.day')
                }
            },
            yAxis: {
                title: {
                    text: 'Кол-во/Сумма'
                },
                min: 0
            },
            tooltip: {
                headerFormat: '<b>{series.name}</b><br>',
                pointFormat: '{point.x}: {point.y:.2f}'
            },

            plotOptions: {
                spline: {
                    marker: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle'
            },
            series: [{
                name: trans('messages.cheques.amount'),
                data: chequesAmountByDay
            }],
            responsive: {
                rules: [{
                    condition: {
                        maxWidth: 500
                    },
                    chartOptions: {
                        legend: {
                            layout: 'horizontal',
                            align: 'center',
                            verticalAlign: 'bottom'
                        }
                    }
                }]
            }
        }
        return <Segment>
            <Menu borderless secondary stackable>
                <Menu.Item>
                        <SemanticDatepicker
                                className="datepicker"
                                clearable={true}
                                format='DD.MM.YYYY'
                                width={8}
                                locale={ptLocale}
                                onDateChange={this.handleSearchDate}
                                type="range"
                                selected={this.state.search.dates}
                                />
                    </Menu.Item>
                <Menu.Menu position="right"></Menu.Menu>
            </Menu>
            <Grid centered>
                <Grid.Row columns={3}>
                    <Grid.Column width={4}>
                        {users.fetching
                            ?<Dimmer active><Loader/></Dimmer>
                            :<Statistic>
                                <Statistic.Value><Icon name="user"/>{numeral(users.data.filter((item)=>{return (item.role==='user');}).length).format('0,0')}</Statistic.Value>
                                <Statistic.Label>{trans('messages.users.users_count')}</Statistic.Label>
                            </Statistic>
                        }
                    </Grid.Column>
                    <Grid.Column width={4}>
                        {cheques.fetching
                            ?<Dimmer active><Loader/></Dimmer>
                            :<Statistic color="grey">
                                <Statistic.Value><Icon name="money bill alternate outline"/>{numeral(cheques.data.length).format('0,0')}</Statistic.Value>
                                <Statistic.Label>{trans('messages.cheques.total_count')}</Statistic.Label>
                            </Statistic>
                        }
                    </Grid.Column>
                    <Grid.Column width={8}>
                        {cheques.fetching
                            ?<Dimmer active><Loader/></Dimmer>
                            :<Statistic color="blue">
                                <Statistic.Value>{numeral(cheques.data.reduce( (accumulator,item) => {
                                        let sum = 0;
                                        if(item.data && item.data.positions)item.data.positions.map( (position,i) => {
                                            sum+=position.amount*position.quantity;
                                        })
                                        accumulator += sum;
                                        return accumulator;
                                    },0)).format('0,0.00') } <Icon name="ruble"/></Statistic.Value>
                                <Statistic.Label>{trans('messages.cheques.total_amount')}</Statistic.Label>
                            </Statistic>
                        }
                    </Grid.Column>
                </Grid.Row>
                <Grid.Row columns={2}>
                    <Grid.Column>
                        <HighchartsReact
                            highcharts={Highcharts}
                            options={chartOptions}
                          />
                    </Grid.Column>
                    <Grid.Column>
                        <HighchartsReact
                            highcharts={Highcharts}
                            options={chartAmountOptions}
                          />
                    </Grid.Column>
                </Grid.Row>
                {auth.can.manager?(<Grid.Row columns={1}>
                    <Grid.Column>
                        {
                            (cheques.fetching || users.fetching || contractors.fetching)
                                ?<Dimmer active><Loader/></Dimmer>
                                :<Report cheques={cheques} users={users} contractors={contractors}/>
                        }
                    </Grid.Column>
                </Grid.Row>):null}
                {auth.can.admin?(<Grid.Row columns={1}>
                    <Grid.Column>
                        <Organisations/>
                    </Grid.Column>
                </Grid.Row>):null}
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
            users:state.users,
            cheques:state.cheques,
            contractors:state.contractors
        };
    },
    (dispatch) =>{
        return {
            action: (m,a,data,callback,failback) => {
                dispatch(modelRest(m,a,data,callback,failback));
            }

        };
    }
)(DashboardAdmin);
