import * as React from 'react';
import {Box, Container, makeStyles, Typography} from "@material-ui/core";

const useStyles = makeStyles({
    title: {
        color: '#999'
    }
});

type PageProps = {
    title: string
};
export const Page: React.FC<PageProps> = (props) => {
    const css = useStyles();
    return (
        <Container>
            <Typography className={css.title} component={'h1'} variant={'h5'}>
                {props.title}
            </Typography>
            <Box paddingTop={1}>
                {props.children}
            </Box>
        </Container>
    );
};