import * as React from 'react';
import {Page} from "../../components/Page";
import {Form} from "./Form";

interface PageFormProps {

}

export const PageForm: React.FC = (props: PageFormProps) => {
    return (
        <Page title={'Criar Membros'}>
            <Form />
        </Page>
    );
};