// @flow
import * as React from 'react';
import {IconButton, Menu as MuiMenu, MenuItem} from "@material-ui/core";
import MenuIcon from "@material-ui/icons/Menu";
import routes, {MyRouteProps} from "../../routes";
import {Link} from "react-router-dom";

const listRoutes = ['dashboard', 'categories.list', 'cast_members.list', 'genres.list'];
const menuRotes = routes.filter(route => listRoutes.includes(route.name));

type Props = {};
export const Menu = (props: Props) => {

    const [anchorEl, setAnchorEl] = React.useState(null);
    const open = Boolean(anchorEl);
    const handleOpen = (event: any) => setAnchorEl(event.currentTarget);
    const handleClose = () => setAnchorEl(null);

    return (
        <React.Fragment>
            <IconButton edge={'start'} color={'inherit'} aria-label={'open drawer'} aria-controls={'menu-appbar'}
                        aria-haspopup={true} onClick={handleOpen}>
                <MenuIcon/>
            </IconButton>
            <MuiMenu id={'menu-appbar'} open={open} anchorEl={anchorEl} onClose={handleClose}
                     anchorOrigin={{vertical: 'bottom', horizontal: 'center'}}
                     transformOrigin={{vertical: 'top', horizontal: 'center'}}
                     getContentAnchorEl={null}>
                {
                    listRoutes.map((value, key) => {
                        const route = menuRotes.find(rt => value === rt.name) as MyRouteProps;
                        return (
                            <MenuItem key={key} component={Link} to={route.path as string}
                                      onClick={handleClose}>{route.label}</MenuItem>
                        )
                    })
                }

            </MuiMenu>
        </React.Fragment>
    )
};
