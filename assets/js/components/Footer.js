import React, { Component } from 'react';

class Footer extends Component {
    constructor(props) {
        super(props);
    }
    render() {
        let [regions, subregions] = this.generateRegionLists();
        let regionPart = 'No data to display yet.';
        let subregionPart = 'No data to display yet.';

        if (regions.length > 0) {
            regionPart = regions.map(region => (
                <span className="stat-item stat-region" key={region['name']}>{region['name']} (x{region['count']})</span>
            ));
        }
        if (subregions.length > 0) {
            subregionPart = subregions.map(subregion => (
                <span className="stat-item stat-subregion" key={subregion['name']}>{subregion['name']} (x{subregion['count']})</span>
            ));
        }

        return (
            <footer>
                <div className="stats-row">
                    <div className="stats-column-header">
                        Countries:
                    </div>
                    <div className="stats-column-data">
                        { this.props.countries.length }
                    </div>
                </div>
                <div className="stats-row">
                    <div className="stats-column-header">
                         Regions:
                    </div>
                    <div className="stats-column-data">
                        {regionPart}
                    </div>
                </div>
                <div className="stats-row">
                    <div className="stats-column-header">
                        Subregions:
                    </div>
                    <div className="stats-column-data">
                        {subregionPart}
                    </div>
                </div>
            </footer>
        )
    }
    generateRegionLists() {
        let regions = [];
        let subregions = [];

        this.props.countries.forEach(function (country) {
            let region = regions.filter(region => region['name'] === country.region);
            let subregion = subregions.filter(subregion => subregion['name'] === country.subregion);
            if (region.length > 0) {
                region[0]['count']++;
            } else if (country.region !== '') {
                regions.push({'name' : country.region, count: 1})
            }
            if (subregion.length > 0) {
                subregion[0]['count']++;
            } else if (country.subregion !== '') {
                subregions.push({'name' : country.subregion, count: 1})
            }
        });

        return [regions, subregions];
    }
}

export default Footer;
