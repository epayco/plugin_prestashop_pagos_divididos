<?php
/**
 * Clase en donde se guardan las transacciones
 */

class EpaycoRules extends ObjectModel{
	public $id;
	public $id_payco;
	public $order_id;
	public $order_stock_restore;
	public $order_stock_discount;
	public $order_status;
	
	public static $definition = array(
		'table' => _DB_PREFIX_.'payco_rules',
		'primary' => 'id',
		'multilang' => false,
		'fields' => array(
				'id' => array('type' => self::TYPE_INT, 'required' => false),
				'p_cust_id' => array('type' => self::TYPE_INT, 'required' => false),
				'payment_method' => array('type' => self::TYPE_STRING, 'required' => false),
                'payment_method_value' => array('type' => self::TYPE_STRING, 'required' => false),
				'customer_id' => array('type' => self::TYPE_STRING, 'required' => false),
				'email' => array('type' => self::TYPE_STRING, 'required' => false),
				'dues' => array('type' => self::TYPE_INT, 'required' => false),
				'doc_type' => array('type' => self::TYPE_STRING, 'required' => false),
				'doc_number' => array('type' => self::TYPE_STRING, 'required' => false)
		)
	);
	
	/**
	 * Guarda el registro de una regla de split
	 * @param string $customer_id
	 * @param array $stock
	 */
	public static function create($p_cust_id,$payment_method,$token,$customer_id,$customer_email,$dues,$doc_type,$doc_number)
	{
		if($customer_id == "" && $token =="" && $customer_email =="" ){
			return false;
		}else{
			if($customer_id =="")
				return false;

			if($token =="")
				return false;

			if($customer_email =="")
				return false;

        try {
            $db = Db::getInstance();
            $result_ = $db->execute('
            INSERT INTO `'._DB_PREFIX_.'payco_rules`
            ( `p_cust_id`, `payment_method`, `payment_method_value`, `customer_id`, `email` ,`dues` ,`doc_type`,`doc_number`)
            VALUES
            ("'.intval($p_cust_id).'","'.$payment_method.'","'.$token.'","'.$customer_id.'","'.$customer_email.'","'.intval($dues).'","'.$doc_type.'","'.$doc_number.'")');
            if($result_){
                return true;
                }else{
                    return false;
                }
            } catch (\Throwable $th) {
                echo $th;
                die();
            }
		}
	}


	public static function CustomerUpdate($p_cust_id,$payment_method,$token,$customer_id,$customer_email,$dues,$doc_type,$doc_number)
	{
		if($customer_id == "" && $token =="" && $customer_email =="" ){
			return false;
		}else{
			if($customer_id =="")
				return false;

			if($token =="")
				return false;

			if($customer_email =="")
				return false;

        try {
            $db = Db::getInstance();
            $request = 'SELECT * FROM `'.EpaycoRules::$definition['table'].'`WHERE `email` = "'.$customer_email.'" ';
            /** @var array $result */
            $result2 = $db->executeS($request);
            if (count($result2) > 0){
                $id = $result2[0]['id'];
                $result_ = $db->update('payco_rules',
                    array(
                        'payment_method' => $payment_method,
                        'payment_method_value' => $token,
                        'dues' => $dues,
                        'doc_type' => $doc_type,
                        'doc_number' => $doc_number,
                    ), 'id = '.trim($id));

                if($result_) {
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }

            } catch (\Throwable $th) {
                echo $th;
                die();
            }
		}
	}

	/**
	 * Eliminar regla de split
	 * @param string $customer_id
	 * @param string $id_
	 */	
	public static function deleteSplitRule($customer_id,$id_)
	{
		try {
            $db = Db::getInstance();
            $db->execute('
            DELETE FROM `'._DB_PREFIX_.'payco_rules`
            WHERE id = "'.intval($id_).'"');
        } catch (\Throwable $th) {
            echo $th;
            die();
        }
    }


	/**
	 * Consultar si existe el registro de una oden
	 * @param int $orderId
	 */	
	public static function ifExist($email)
	{  
		$db = Db::getInstance();
		$request = 'SELECT * FROM `'.EpaycoRules::$definition['table'].'`WHERE `email` = "'.$email.'" ';
			/** @var array $result */
			$result2 = $db->executeS($request);		
		if (count($result2) > 0){
			return $result2;
		}else{
			return false;
		}
	}

	/**
	 * Consultar si a una orden ya se le descconto el stock
	 * @param int $orderId
	 */	
	public static function ifStockDiscount($orderId)
	{	
		$db = Db::getInstance();
		$result = $db->getRow('
			SELECT `order_stock_discount` FROM `'.EpaycoOrder::$definition['table'].'`
			WHERE `order_id` = "'.intval($orderId).'"');

		return intval($result["order_stock_discount"]) != 0 ? true : false;
		
	}

	/**
	 * Actualizar que ya se le descontó el stock a una orden
	 * @param int $orderId
	 */	
	public static function updateStockDiscount($orderId)
	{
		$db = Db::getInstance();
		$result = $db->update('payco_rules', array('order_stock_discount'=>1), 'order_id = '.(int)$orderId );

		return $result ? true : false;
	}
	

	/**
	 * Crear la tabla en la base de datos.
	 * @return true or false
	 */
	public static function setup()
	{
		$sql = array();
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payco_rules` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `p_cust_id` INT(11) NULL,
		    `payment_method` TEXT NULL,
		    `payment_method_value` TEXT NULL,
		    `customer_id` TEXT NULL,
			`email` TEXT NULL,
			`dues` int(4) NOT NULL,
			`doc_type` TEXT NULL,
			`doc_number` TEXT NULL,
		    PRIMARY KEY  (`id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

		foreach ($sql as $query) {
		    if (Db::getInstance()->execute($query) == false) {
		        return false;
		    }
		}
	}

	/**
	 * Borra la tabla en la base de datos.
	 * @return true or false
	 */
	public static function remove(){
		$sql = array(
				'DROP TABLE IF EXISTS '._DB_PREFIX_.'payco_rules'
		);

		foreach ($sql as $query) {
		    if (Db::getInstance()->execute($query) == false) {
		        return false;
		    }
		}
	}
}