import * as React from 'react';
import {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {Chip} from "@material-ui/core";
import {format, parseISO} from 'date-fns';
import categoryHttp from "../../util/http/category-http";

const columnsDefinition: MUIDataTableColumn[] = [
    {label: 'Nome', name: 'name'},
    {
        label: 'Ativo', name: 'is_active', options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value ? <Chip label={'Sim'} color={'primary'}/> : <Chip label={'Não'} color={'secondary'}/>;
            }
        }
    },
    {label: 'Criando em', name: 'created_at',options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }},
];

// const options: MUIDataTableOptions = {
//     filterType: 'checkbox',
// };

interface Category {
    id: string;
    name: string;
}

type TableProps = {};
export const Table: React.FC = (props: TableProps) => {

    const [data, setData] = useState<Category[]>([]);
    useEffect(() => {
        categoryHttp.list<{data: Category[]}>().then(
            response => setData(response.data.data)
        )
    }, []);

    return (
        <div>
            <MUIDataTable
                title={"Categorias"}
                data={data}
                columns={columnsDefinition}
                // options={options}
            />
        </div>
    );
};