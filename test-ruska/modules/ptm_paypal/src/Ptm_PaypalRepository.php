<?php
/**
* 2016 - 2017 PrestaBuilder
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future.
*
*  @author    PrestaBuilder <prestabuilder@gmail.com>
*  @copyright 2016 - 2017 PrestaBuilder
*  @license   Do not distribute this module without permission from the author
*/

class Ptm_PaypalRepository
{
    private $db;
    private $shop;
    private $db_prefix;

    public function __construct(Db $db)
    {
        $this->db = $db;
        $this->db_prefix = $db->getPrefix();
    }

    public function createTables()
    {
        $engine = _MYSQL_ENGINE_;
        $success = true;
        // $this->dropTables();

        $query = "CREATE TABLE IF NOT EXISTS `{$this->db_prefix}ptm_paypal`(
    			`id_ptm_paypal` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `trans_id` varchar(30) NOT NULL,
                `payment_date` varchar(60) DEFAULT NULL,
                `mc_gross` varchar(20) DEFAULT NULL,
                `mc_currency` varchar(10) DEFAULT NULL,
                `mc_fee` varchar(10) DEFAULT NULL,
                `protection_eligibility` varchar(30) DEFAULT NULL,
                `address_status` varchar(30) DEFAULT NULL,
                `payer_id` varchar(40) DEFAULT NULL,
                `payer_status` varchar(10) DEFAULT NULL,
                `payer_email` varchar(80) DEFAULT NULL,
                `ipn_track_id` varchar(10) DEFAULT NULL,
                `verify_sign` varchar(100) DEFAULT NULL,
                `test_ipn` varchar(30) NOT NULL,
                `id_cart` int(11) NOT NULL,
                `created_at` datetime NOT NULL
            ) ENGINE=$engine DEFAULT CHARSET=utf8";

        $success &= $this->db->execute($query);

        return $success;
    }

    public function dropTables()
    {
        $sql = "DROP TABLE IF EXISTS `{$this->db_prefix}ptm_paypal`";

        return $this->db->execute($sql);
    }

    public function add($trans_id, $payment_type, $payment_date, $mc_gross, $mc_currency, $mc_fee, $protection_eligibility, $address_status, $payer_id, $payer_status, $payer_email, $ipn_track_id, $verify_sign, $mode, $id_cart)
    {
        $created_at = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `{$this->db_prefix}ptm_paypal` (`trans_id`,`payment_date`,`mc_gross`,`mc_currency`,`mc_fee`,`protection_eligibility`,`address_status`,`payer_id`,`payer_status`,`payer_email`,`ipn_track_id`,`verify_sign`,`test_ipn`,`id_cart`,`created_at`) VALUES ('". pSQL($trans_id) ."', '". pSQL($payment_date) ."', '". pSQL($mc_gross) ."', '". pSQL($mc_currency) ."', '". pSQL($mc_fee) ."', '". pSQL($protection_eligibility) ."', '". pSQL($address_status) ."', '". pSQL($payer_id) ."', '". pSQL($payer_status) ."', '". pSQL($payer_email) ."', '". pSQL($ipn_track_id) ."', '". pSQL($verify_sign) ."', '". pSQL($mode) .'#'. pSQL($payment_type) ."', '". (int)$id_cart ."', '". pSQL($created_at) ."')";

        return $this->db->execute($sql);
    }
}
