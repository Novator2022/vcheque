import React, { Component } from 'react'
import {Icon} from 'semantic-ui-react';
import numeral from 'numeral';
import axios from 'axios';

export class Amount extends Component {
    constructor(props){
        super(props);
    }
    render() {
        let {value} = this.props;
        value = parseFloat(value);
        if(!value) return <span className="amount"></span>;

        return <span className="amount">{numeral(value).format('0.00')}</span>;
    }
}

export class Date extends Component {
    constructor(props){
        super(props);
    }
    render() {
        let {value} = this.props;
        if(!value) return <span className="date"></span>;
        value = value.replace(/^(\d{4})\-(\d{2})\-(\d{2})\s?(\d{2}):(\d{2}):(\d{2})$/,"$3.$2.$1");
        return <span className="date"><Icon name="calendar"/>{value}</span>;
    }
}

export class DateTime extends Component {
    constructor(props){
        super(props);
    }
    render() {
        let {value} = this.props;
        if(!value) return <span className="date"></span>;
        const date = value.replace(/^(\d{4})\-(\d{2})\-(\d{2})\s?(\d{2}):(\d{2}):(\d{2})$/,"$3.$2.$1");
        const time = value.replace(/^(\d{4})\-(\d{2})\-(\d{2})\s?(\d{2}):(\d{2}):(\d{2})$/,"$4:$5:$6");
        return <span className="date"><Icon name="calendar"/>{date}<br/><Icon name="clock"></Icon>{time}</span>;
    }
}

export class Time extends Component {
    constructor(props){
        super(props);
    }
    render() {
        let {value} = this.props;
        if(!value) return <span className="date"></span>;

        const time = value.replace(/^(\d{4})\-(\d{2})\-(\d{2})\s?(\d{2}):(\d{2}):(\d{2})$/,"$4:$5:$6");
        return <span className="date"><Icon name="clock"></Icon>{time}</span>;
    }
}

export class FileUpload extends Component {
    constructor(props) {
        super(props);
        this.upload = this.upload.bind(this);
    }
    upload(e) {
        e.preventDefault();

        const {url, name, onUploaded, onFailed, onBefore} = this.props;
        const fileInput = e.target;
        const p = new FormData();
        p.append(name, e.target.files[0]);
        if (onBefore) {
            onBefore(p);
        }

        const ax = axios( {
            url: url,
            headers: { 'Content-Type': 'multipart/form-data', 'X-CSRF-TOKEN': document.getElementsByTagName("meta")['csrf-token'].content },
            method: 'POST',
            data: p
        }).then((response) => {
            if(onUploaded) {
                onUploaded(response);
            }
        }).catch((error) => {
            if(onFailed) {
                onFailed(error.response || error);
            }

            fileInput.value = '';
        })
    }
    render(){
        const {name, value, label} = this.props;
        return <div class="field">
            {label ? <label>{label}</label> : null}
            <input name={name} value={value} onChange={this.upload} type="file" className="ui basic label green input" ref={(ref) => {this.fileUpload = ref;}} placeholder="Загрузите файл"/>
        </div>;
    }
}
