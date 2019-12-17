import {PageList as MemberList} from "../pages/members/PageList";
import {MyRouteProps} from "./index";

export const castMembersroutes: MyRouteProps[] = [
    {
        name: 'cast_members.list',
        label: 'Listar Membros',
        path: '/cast_members',
        component: MemberList,
        exact: true
    },
    {
        name: 'cast_members.create',
        label: 'Criar Membro',
        path: '/cast_members/create',
        component: MemberList,
        exact: true
    },
    {
        name: 'cast_members.edit',
        label: 'Editar Membro',
        path: '/cast_members/:id/edit',
        component: MemberList,
        exact: true
    }
];