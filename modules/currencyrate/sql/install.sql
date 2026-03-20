CREATE TABLE IF NOT EXISTS `PREFIX_currencyrate_history` (
    `id_currencyrate_history` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `iso_code` VARCHAR(3) NOT NULL,
    `effective_date` DATE NOT NULL,
    `mid` DECIMAL(20, 10) NOT NULL,
    `table_no` VARCHAR(24) NOT NULL,
    `table_type` VARCHAR(1) NOT NULL DEFAULT "A",
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_currencyrate_history`),
    UNIQUE KEY `uniq_currency_day` (`iso_code`, `effective_date`),
    KEY `idx_effective_date` (`effective_date`)
) ENGINE=ENGINE_ DEFAULT CHARSET=utf8mb4;
