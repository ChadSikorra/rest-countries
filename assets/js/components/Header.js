import React, { Component } from 'react';

const FILTER_MAP = {
    'all': 'Name or Code',
    'name': 'Name Only',
    'code': 'Code Only'
};

class Header extends Component {
    constructor(props) {
        super(props);
        this.state = {
            filter: 'all',
            search: '',
        };
        this.onSearchChange = this.onSearchChange.bind(this);
        this.onKeyPressed = this.onKeyPressed.bind(this);
        this.onFilterChange = this.onFilterChange.bind(this);
    }
    render() {
        return (
            <header>
                <label htmlFor="search">Search:</label>
                <input id='search'
                       autoFocus
                       onChange={this.onSearchChange}
                       onKeyDown={this.onKeyPressed}
                       placeholder={"Country " + FILTER_MAP[this.state.filter] + "..."}
                       value={this.state.search}
                />
                <select id="filter" onChange={this.onFilterChange} value={this.state.filter}>
                    <option value="all">{FILTER_MAP.all}</option>
                    <option value="name">{FILTER_MAP.name}</option>
                    <option value="code">{FILTER_MAP.code}</option>
                </select>
            </header>
        )
    }
    onFilterChange(e) {
        this.setState({filter: e.target.value}, this.refreshData);
    }
    onKeyPressed(e) {
        if (e.key === 'Enter') {
            this.refreshData(true)
        }
    }
    onSearchChange(e) {
        this.setState({ search: e.target.value}, this.refreshData);
    }
    refreshData(enterKey = false) {
        if (this.state.search === '') {
            let data = enterKey ? {'error': 'The search cannot be empty.'} : [];
            this.props.onSearchComplete(data, '');
        } else {
            fetch('/api/country/' + encodeURIComponent(this.state.search) + '?filter=' + this.state.filter)
                .then(response => response.json())
                .then(data => this.props.onSearchComplete(data, search));
        }
    }
}

export default Header;
