import numeral from 'numeral';
export const VDate = {
    fromRuDate: (date) => {
        const vals = date.split(/\./g);
        const ret = new Date(vals[2],vals[1]-1,vals[0],0,0,0);
        return ret;
    },
    fromEnDate: (date) => {
        const vals = date.replace(/^(\d{4}\-\d{2}\-\d{2}).*/, "$1").split(/\-/g);
        const ret = new Date(vals[0], vals[1]-1, vals[2], 0, 0, 0);
        return ret;
    },
    toEnDate: (date) => {
        const year = date.getFullYear();
        const month = numeral( date.getMonth() + 1 ).format('00');
        const day = numeral( date.getDate() ).format('00');
        return `${year}-${month}-${day}`;
    },
    daysIn: (first,last) => {
        let ret = [];
        let current = first;
        while( current.getTime() <= last.getTime() ){
            ret.push(current);
            current = VDate.addDays(current,1) ;
        }
        return ret;
    },
    addDays: (dateFrom,days) => {
        const date = new Date( dateFrom.valueOf());
        date.setDate(date.getDate() + days);
        return date;
    },
    currentMonth: () => {
        return [ VDate.monthFirstDay(), VDate.monthLastDay() ];
    },
    currentMonthEn: () => {
        const dates = VDate.currentMonth();
        dates[0] = VDate.toEnDate( dates[0] );
        dates[1] = VDate.toEnDate( dates[1] );
        return dates;
    },
    monthFirstDay: () => {
        const d = new Date();
        const ret = new Date(d.getFullYear(),d.getMonth(),1);
        return ret;
    },
    monthLastDay: () => {
        const d = new Date();
        let year = d.getFullYear();
        let month = d.getMonth()+1;
        if(month>=12){
            year++;
            month=0;
        }
        let ret = new Date(year,month,1,23,59,59);
        ret = VDate.addDays(ret,-1);
        return ret;
    },
    truncDate: (d, end = false) => {
        let year = d.getFullYear();
        let month = d.getMonth()+1;
        let day = d.getDate();
        if(month<10){
            month = `0${month}`;
        }
        if(day<10){
            day = `0${day}`;
        }
        let his = '00:00:00';
        if (end) {
            his = '23:59:59';
        }
        return `${year}-${month}-${day} ${his}`;
    }
}
