insert into admin (id, username, roles, password)
values(nextval('admin_id_seq'), 'admin', '[\"ROLE_ADMIN\"]',
       '\$argon2id\$v=19\$m=65536,t=4,p=1\$25hJ/zZNEiYtmThka24xPg\$4deeCYRo7+d7aXRx9X0On0/1gVxPWKSC47cezNRJZyk');
