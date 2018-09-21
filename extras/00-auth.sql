PRAGMA foreign_keys = on; --habilito las impresindibles claves foraneas
PRAGMA journal_mode = WAL;--probar esto para tener algo de concurrencia

drop table if exists users;
create table if not exists users(
    id_user                 integer      primary key AUTOINCREMENT,
    fullname                varchar(64)  not null,                                       /*nombre completo*/
    username                varchar(32)  not null,                                       /*aka,alias,sobrenombre de este usuario*/
    image                   varchar(32)  default null,                                   /*url a la imagen o avatar del user*/
    password                varchar(512) not null,                                       /*contraseña de este usuario hash whirlpool*/
    session_persistent      varchar(1)   not null check (status in ('f','t')) default 't',/*define si la sesion es persistente*/
    registre_date           datetime     default (datetime('now','localtime')),          /*la fecha de este registro*/
    status                  varchar(1)   not null check (status in ('f','t')) default 't'/*estatus de este usuario t = activo f = inactivo*/
);
create unique index if not exists users_id_user  on users (id_user);
create unique index if not exists users_username on users (username);
insert into users (fullname,username,password)values('Máster Vitronic','vitronic','$2y$10$asjC6nRZEamPkecIRMDjTec1lpvGFGPfHgOOcUnUXFz5r5bLRXFHS');
insert into users (fullname,username,password)values('Johana Urbaneja','johana','$2y$10$rXk5fn9AsOTrjzVnY6C6WOTLHA.vA4uBMkzM.ubsu3JGPoudWlYAu');
insert into users (fullname,username,password)values('Victor Eduardo','vector','$2y$10$wRBDyOplZCDB9Ks7hKUCD.Gyz.hZfKc3XET1UpzVThQA6nUHM/qby');
insert into users (fullname,username,password)values('Victor Diex','diex','$2y$10$EShqegKBU13zRbUCb/Gbk.pO03N5bo.g./WqbJzAn/GAaPNbFBGRu');
insert into users (fullname,username,password)values('Victor Diego','diego','$2y$10$UWiV0bqGc75eq3ijrSC/r.mUqddQEywCXJNv6qYxJFtzybnMwYdjG');
