import * as React from 'react';
import {Box, Button, ButtonProps, Checkbox, makeStyles, TextField, Theme} from "@material-ui/core";
import useForm from "react-hook-form";
import categoryHttp from "../../util/http/category-http";
import {useHistory} from "react-router-dom";
import {categoriesRoutes} from "../../routes/categories";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});

type FormProps = {};
export const Form = (props: FormProps) => {
    const categories_list = categoriesRoutes.filter(x => x.name === 'categories.list').map(x => x.path)[0];

    const css = useStyles();
    const history = useHistory();

    const buttonProps: ButtonProps = {
        className: css.submit,
        variant: "outlined",
        size: "medium",
    };

    const defaultValues = {
        is_active: true
    };
    const {register, handleSubmit, getValues, reset, formState} = useForm({
        defaultValues
    });

    function onSubmit(formData, event) {
        if (!formState.isValid) {
            return;
        }
        categoryHttp.create(formData)
            .then(response => {
                if (!event) {
                    history.push(categories_list as string);
                } else {
                    reset(defaultValues);
                }
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
                inputRef={register}
                name={'description'}
                label={'Descrição'}
                multiline
                rows={4}
                fullWidth
                variant={"outlined"}
                margin={"normal"}
                required
            />
            <Checkbox
                inputRef={register}
                name={'is_active'}
                defaultChecked
            />
            Ativo?
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