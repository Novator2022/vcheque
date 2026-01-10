

export function paginate(data, page = 1){
    const ret = {
        data:[],
        total: data.length,
        per_page: 12,
        from: 0,
        to: 0,
        page: page,
        pages: 0
    };

    ret.pages = Math.ceil(ret.total / ret.per_page);

    const startPosition = (ret.per_page - 1) * (page - 1);
    ret.from = startPosition;
    ret.to = (startPosition + ret.per_page) - 1;
    for (let i = startPosition; i < (startPosition + ret.per_page); ++i) {
        const item = data[i];
        if (item) {
            ret.data.push(item);
        }
        else {
            break;
        }
    }

    return ret;
}
