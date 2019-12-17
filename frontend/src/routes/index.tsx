import {RouteProps} from 'react-router-dom'
import {Dashboard} from "../pages/Dashboard";
import {castMembersroutes} from "./cast_members";
import {categoriesRoutes} from "./categories";
import {genresRoutes} from "./genres";

export interface MyRouteProps extends RouteProps {
    label: string,
    name: string,
    exact: boolean
}

const routes: MyRouteProps[] = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        path: '/',
        component: Dashboard,
        exact: true
    },
    ...castMembersroutes,
    ...categoriesRoutes,
    ...genresRoutes
];

export default routes;