import * as React from 'react';
import {
    Box,
    Button,
    ButtonProps,
    FormControlLabel,
    makeStyles,
    Radio,
    RadioGroup,
    TextField,
    Theme
} from "@material-ui/core";
import useForm from "react-hook-form";
import cast_membersHttp from "../../util/http/cast_members-http";
import {useEffect} from "react";

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

    const {register, handleSubmit, getValues, formState, setValue} = useForm();

    useEffect(() => {
        register({name: 'type'})
    }, [register]);

    function onSubmit(formData, event) {
        if (!formState.isValid) {
            return;
        }
        cast_membersHttp.create(formData)
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
            <RadioGroup name={'type'}
            onChange={(e) => {
                setValue('type', parseInt(e.target.value));
            }}>
                <FormControlLabel value={'1'} control={<Radio/>} label={'Diretor'}/>
                <FormControlLabel value={'2'} control={<Radio/>} label={'Ator'}/>
            </RadioGroup>
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