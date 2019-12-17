import * as React from 'react';
import {Page} from "../../components/Page";
import {Box, Fab} from "@material-ui/core";
import {Link} from "react-router-dom";
import AddIcon from '@material-ui/icons/Add';
import {Table} from "./Table";

interface PageListProps {

}

export const PageList: React.FC = (props: PageListProps) => {
    return (
        <Page title={'Listar Gêneros'}>
            <Box dir={'rtl'}>
                <Fab
                    title={'AdicionarGenero'}
                    size={'small'}
                    component={Link}
                    to={'/genres/create'}>
                    <AddIcon/>
                </Fab>
            </Box>
            <Box>
                <Table></Table>
            </Box>
        </Page>
    );
};