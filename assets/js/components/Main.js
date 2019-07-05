import React from 'react';
import Country from "./Country";

function Main (props) {
    if (props.error) {
        return <main><div className='message-container'><div className="message message-error">{props.errorMessage}</div></div></main>;
    }
    if (props.search === '' || props.search === null) {
        return <main><div className='message-container'><div className="message message-info">No data to display yet.</div></div></main>;
    }
    if (props.countries.length === 0) {
        return <main><div className='message-container'><div className="message message-error">The search returned no results.</div></div></main>;
    }

    return (
        <main>
            <div className="country-container">
            {props.countries.map(country => (
                <Country country={country} key={country.name} />
            ))}
            </div>
        </main>
    );
}

export default Main;
