import * as React from 'react';
import {useEffect, useState} from 'react';
import {Box, Button, ButtonProps, makeStyles, MenuItem, TextField, Theme} from "@material-ui/core";
import useForm from "react-hook-form";
import categoryHttp from "../../util/http/category-http";
import genresHttp from "../../util/http/genres-http";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});

type FormProps = {};

export const Form = (props: FormProps) => {
    const css = useStyles();

    const buttonProps: ButtonProps = {
        className: css.submit,
        variant: "outlined",
        size: "medium",
    };

    const [categories, setCategories] = useState<any[]>([]);
    const {register, handleSubmit, getValues, formState, setValue, watch} = useForm({
        defaultValues: {categories_id: []}
    });

    useEffect(() => {
        register({name: 'categories_id'})
    }, [register]);

    useEffect(() => {
        categoryHttp.list().then(response => setCategories(response.data.data));
    }, []);

    function onSubmit(formData, event) {
        if (!formState.isValid) {
            return;
        }
        genresHttp.create(formData)
            .then(response => {
                console.log(response);
            });
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <TextField
                inputRef={register}
                name={'name'}
                label={'Nome'}
                fullWidth
                variant={"outlined"}
                margin={"normal"}
                required
            />

            <TextField
                select
                name={'categories_id'}
                value={watch('categories_id')}
                label={'Categorias'}
                margin={'normal'}
                variant={'outlined'}
                fullWidth
                onChange={(e) => {
                    setValue('categories_id', e.target.value);
                }}
                SelectProps={{multiple: true}}>
                <MenuItem value="" disabled>
                    <em>Selecionar Categorias</em>
                </MenuItem>
                {
                    categories.map(
                        (category, key) => (
                            <MenuItem key={key} value={category.id}>{category.name}</MenuItem>
                        )
                    )
                }
            </TextField>

            <Box dir={'rtl'}>
                <Button {...buttonProps}
                        onClick={() => onSubmit(getValues(), null)}
                >Salvar</Button>
                <Button {...buttonProps}
                        type={'submit'}
                >Salvar e continuar editando</Button>
            </Box>
        </form>
    );
};