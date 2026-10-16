CREATE OR REPLACE VIEW `vwleadcalltots` AS select distinct `vwleadcalls`.`legacy_id` AS `legacy_id`,`vwleadcalls`.`numero_chiamato` AS `numero_chiamato` from `vwleadcalls`
