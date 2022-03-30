CREATE TABLE `sys_account`
(
    `id`          int unsigned NOT NULL AUTO_INCREMENT,
    `username`    varchar(64) NOT NULL,
    `surname`     varchar(64) NOT NULL,
    `telephone`   varchar(16) DEFAULT '',
    `password`    varchar(64) NOT NULL,
    `sign`        varchar(16) NOT NULL,
    `create_time` datetime    DEFAULT CURRENT_TIMESTAMP,
    `is_delete`   tinyint     DEFAULT '0',
    `role_id`     int unsigned NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统账户';

-- 账号admin 密码123
insert into sys_account(username, surname, password, sign, role_id)
values ("admin", "管理员", "29f171081bddb57744d7fc587e7c34de", "xJddkjd93jdd", 0);


CREATE TABLE `sys_role`
(
    `id`          int unsigned NOT NULL AUTO_INCREMENT,
    `role_name`   varchar(50) NOT NULL,
    `role_desc`   varchar(200) DEFAULT '',
    `create_time` datetime     DEFAULT CURRENT_TIMESTAMP,
    `modify_time` datetime     DEFAULT CURRENT_TIMESTAMP,
    `is_delete`   tinyint      DEFAULT '0',
    `status`      tinyint      DEFAULT '1' COMMENT '1正常 0无效',
    `menus`       text        NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='角色';


CREATE TABLE `sys_menu`
(
    `id`           int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name`         varchar(32) NOT NULL COMMENT '名称',
    `remark`       varchar(256) DEFAULT '' COMMENT '备注',
    `module`       varchar(32) NOT NULL COMMENT '模块',
    `controller`   varchar(32) NOT NULL COMMENT '控制器',
    `action`       varchar(32) NOT NULL COMMENT '方法',
    `icon`         varchar(64)  DEFAULT '' COMMENT '图标',
    `is_display`   tinyint(4) DEFAULT '0' COMMENT '1显示 0不显示',
    `display_type` tinyint(4) DEFAULT '1' COMMENT '1normal 2_blank 3alert',
    `is_check`     tinyint(4) DEFAULT '1' COMMENT '1校验 0不校验',
    `level`        tinyint(4) NOT NULL,
    `fid`          int(11) unsigned NOT NULL,
    `create_time`  datetime     DEFAULT CURRENT_TIMESTAMP,
    `modify_time`  datetime     DEFAULT CURRENT_TIMESTAMP,
    `sort_num`     int(11) unsigned DEFAULT '0',
    `params`       varchar(255) DEFAULT '' COMMENT '参数',
    `status`       tinyint(4) DEFAULT '1' COMMENT '1正常 0无效',
    `is_delete`    tinyint      DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜单';

