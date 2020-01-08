import {PageList as GenreList} from "../pages/genres/PageList";
import {PageForm as GenreForm} from "../pages/genres/PageForm";
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
        component: GenreForm,
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