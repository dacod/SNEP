/* Criação do campo para senhas dos ramais. Independentes da senha do SIP/IAX2*/
ALTER TABLE `peers` ADD `password` VARCHAR(12) NOT NULL;
UPDATE `peers` SET `password`=`secret`;