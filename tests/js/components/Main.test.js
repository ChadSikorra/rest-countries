import React from 'react';
import { shallow } from 'enzyme';

import Main from '../../../assets/js/components/Main';

describe('Main component', () => {
    test('It renders.', () => {
        let wrapper = shallow(<Main countries={[]}/>);

        expect(wrapper.exists()).toBe(true);
    });
});
