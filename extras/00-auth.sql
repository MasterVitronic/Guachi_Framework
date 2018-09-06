PRAGMA foreign_keys = on; --habilito las impresindibles claves foraneas
PRAGMA journal_mode = WAL;--probar esto para tener algo de concurrencia

drop table if exists users;
create table if not exists users( --Tabla usuarios 
    id_user                 integer      primary key AUTOINCREMENT,
    fullname                varchar(64)  not null,                                       /*nombre completo*/
    username                varchar(32)  not null,                                       /*aka,alias,sobrenombre de este usuario*/
    image                   varchar(32)  default null,                                   /*url a la imagen o avatar del user*/
    password                varchar(512) not null,                                       /*contraseña de este usuario hash whirlpool*/
    registreDate            datetime     default (datetime('now','localtime')),          /*la fecha de este registro*/
    status                  varchar(1)   not null check (status in ('f','t')) default 't'/*estatus de este usuario t = activo f = inactivo*/
);
create unique index if not exists users_id_user  on users (id_user);
create unique index if not exists users_username on users (username);
insert into users (fullname,username,password)values('Máster Vitronic','vitronic','$2y$10$sAfhvt49gueW3u59eoJ30OMju6JFlFIS7SAu/dbFzuuWXJpN.yr7m');

