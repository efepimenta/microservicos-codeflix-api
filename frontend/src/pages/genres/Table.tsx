import * as React from 'react';
import {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {httpVideo} from "../../util/http";
import {format, parseISO} from 'date-fns';
import {Chip} from "@material-ui/core";

const columnsDefinition: MUIDataTableColumn[] = [
    {label: 'Nome', name: 'name'},
    {
        label: 'Categorias', name: 'categories', options: {
            customBodyRender(value, tableMeta, updateValue) {
                const categories: any[] = value.map((cat: any) => cat.name);
                return categories.join(', ');
            }
        }
    },
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

type TableProps = {};
export const Table: React.FC = (props: TableProps) => {

    const [data, setData] = useState([]);
    useEffect(() => {
        httpVideo.get('genres').then(
            response => setData(response.data.data)
        )
    }, []);

    return (
        <div>
            <MUIDataTable
                title={"Gêneros"}
                data={data}
                columns={columnsDefinition}
                // options={options}
            />
        </div>
    );
};