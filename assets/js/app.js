import React, { Component } from 'react';
import ReactDom from 'react-dom';
import Footer from "./components/Footer";
import Header from "./components/Header";
import Main from "./components/Main";

require('../css/app.css');

class App extends Component {
    constructor(props) {
        super(props);
        this.state = {
            countries: [],
            error: false,
            errorMessage: '',
            search: '',
        };
        this.handleSearchComplete = this.handleSearchComplete.bind(this);
    }
    render() {
        return (
            <div className="app">
                <Header onSearchComplete={this.handleSearchComplete} />
                <Main countries={this.state.countries}
                      error={this.state.error}
                      errorMessage={this.state.errorMessage}
                      search={this.state.search}/>
                <Footer countries={this.state.countries} />
            </div>
        )
    }
    handleSearchComplete(data, search) {
        let error = data.hasOwnProperty('error');
        let countries = error ? [] : data;
        let errorMessage = error  ? data.error : '';

        this.setState({countries: countries, error: error, errorMessage: errorMessage, search: search});
    }
}

export default App;

ReactDom.render(<App />, document.getElementById('root'));
