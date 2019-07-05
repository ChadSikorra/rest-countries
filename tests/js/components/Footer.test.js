import React from 'react';
import { shallow } from 'enzyme';

import Footer from '../../../assets/js/components/Footer';

describe('Footer component', () => {
    test('It renders.', () => {
        let wrapper = shallow(<Footer countries={[]}/>);

        expect(wrapper.exists()).toBe(true);
    });
});
