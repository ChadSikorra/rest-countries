import React from 'react';
import '../../css/country.css'

const Country = ({ country }) => (
    <div className='country'>
        <div className="country-header">{country.name}</div>
        <div className='country-column-flag'><img src={country.flag} alt={country.name}/></div>
        <div className="country-column-data">
            <div className="country-row">
                <div className="country-data-header">Alpha-2 Code:</div>
                <div className="country-column-data">{country.alpha2Code}</div>
            </div>
            <div className="country-row">
                <div className="country-data-header">Alpha-3 Code:</div>
                <div className="country-column-data">{country.alpha3Code}</div>
            </div>
            <div className="country-row">
                <div className="country-data-header">Region:</div>
                <div className="country-column-data">{country.region ? country.region : '(No Data)'}</div>
            </div>
            <div className="country-row">
                <div className="country-data-header">Subregion:</div>
                <div className="country-column-data">{country.subregion ? country.subregion : '(No Data)'}</div>
            </div>
            <div className="country-row">
                <div className="country-data-header">Population:</div>
                <div className="country-column-data">{country.population.toLocaleString()}</div>
            </div>
            <div className="country-row">
                <div className="country-data-header">Languages:</div>
                <div className="country-column-data">{country.languages.join(', ')}</div>
            </div>
        </div>
    </div>
);

export default Country;
