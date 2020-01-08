import {PageList as CategoryList} from "../pages/category/PageList";
import {PageForm as CategoryForm} from "../pages/category/PageForm";
import {MyRouteProps} from "./index";

export const categoriesRoutes: MyRouteProps[] = [
    {
        name: 'categories.list',
        label: 'Listar Categorias',
        path: '/categories',
        component: CategoryList,
        exact: true
    },
    {
        name: 'categories.create',
        label: 'Criar Categoria',
        path: '/categories/create',
        component: CategoryForm,
        exact: true
    },
    {
        name: 'categories.edit',
        label: 'Editar Categoria',
        path: '/categories/:id/edit',
        component: CategoryList,
        exact: true
    }
];