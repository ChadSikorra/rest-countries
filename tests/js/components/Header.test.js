import React from 'react';
import { shallow } from 'enzyme';

import Header from '../../../assets/js/components/Header';

global.fetch = jest.fn(() => Promise.resolve({
    ok: true,
    json: function() {
        return require('./../../resources/api-response')
    }
}));

describe('Header component', () => {
    test('It renders.', () => {
        let wrapper = shallow(<Header />);

        expect(wrapper.exists()).toBe(true);
    });
});
