import * as React from 'react';
import {useEffect, useState} from 'react';
import MUIDataTable, {MUIDataTableColumn} from "mui-datatables";
import {httpVideo} from "../../util/http";
import {format, parseISO} from 'date-fns';

const columnsDefinition: MUIDataTableColumn[] = [
    {label: 'Nome', name: 'name'},
    {
        label: 'Tipo', name: 'type', options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value === 1 ? <span>Diretor</span> : <span>Ator</span>;
            }
        }
    },
    {
        label: 'Criando em', name: 'created_at', options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    },
];

// const options: MUIDataTableOptions = {
//     filterType: 'checkbox',
// };

type TableProps = {};
export const Table: React.FC = (props: TableProps) => {

    const [data, setData] = useState([]);
    useEffect(() => {
        httpVideo.get('cast_members').then(
            response => setData(response.data.data)
        )
    }, []);

    return (
        <div>
            <MUIDataTable
                title={"Membros"}
                data={data}
                columns={columnsDefinition}
                // options={options}
            />
        </div>
    );
};