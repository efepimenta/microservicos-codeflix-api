import {PageList as GenreList} from "../pages/genres/PageList";
import {MyRouteProps} from "./index";

export const genresRoutes: MyRouteProps[] = [
    {
        name: 'genres.list',
        label: 'Listar Gêneros',
        path: '/genres',
        component: GenreList,
        exact: true
    },
    {
        name: 'genres.create',
        label: 'Criar Gênrero',
        path: '/genres/create',
        component: GenreList,
        exact: true
    },
    {
        name: 'genres.edit',
        label: 'Editar Gênrero',
        path: '/genres/:id/edit',
        component: GenreList,
        exact: true
    }
];