<?php
/**
 * Clase en donde se guardan las transacciones
 */

class SplitRules extends ObjectModel{
	public $id;
	public $id_payco;
	public $order_id;
	public $order_stock_restore;
	public $order_stock_discount;
	public $order_status;
	
	public static $definition = array(
		'table' => _DB_PREFIX_.'payco_split',
		'primary' => 'id',
		'multilang' => false,
		'fields' => array(
				'id' => array('type' => self::TYPE_INT, 'required' => false),
				'customer_id' => array('type' => self::TYPE_INT, 'required' => false),
				'typefeed' => array('type' => self::TYPE_STRING, 'required' => false),
				'feed' => array('type' => self::TYPE_STRING, 'required' => false),
                'avtive' => array('type' => self::TYPE_INT, 'required' => false)
		)
	);
	
	/**
	 * Guarda el registro de una regla de split
	 * @param string $customer_id
	 * @param array $stock
	 */
	public static function create($customer_id,$feed,$typefeed)
	{
		if($customer_id == "" && $feed =="" ){
			echo 0;
			die();
		}else{

			if($customer_id ==""){
				echo 0;
				die();
			}

			if($feed ==""){
				echo 0;
				die();
			}

		try {
			$db = Db::getInstance();
				$request = 'SELECT * FROM `'.SplitRules::$definition['table'].'`WHERE `customer_id` = "'.$customer_id.'"';
				/** @var array $result */
				$result2 = $db->executeS($request);
				$count_ =count($result2);
			if ($count_ > 0){
				echo 1;
				die();
			}else{
				$result_ = $db->execute('
					INSERT INTO `'._DB_PREFIX_.'payco_split`
					( `customer_id`, `typefeed`, `feed`, `avtive`)
					VALUES
					("'.intval($customer_id).'","'.$typefeed.'","'.$feed.'","'.intval('1').'")'
				);
				if($result_){
					$sql = 'SELECT `id_feature`
							FROM `' . _DB_PREFIX_ . 'feature_lang`
							WHERE `name` = ' . '"ePayco receiver"';
					$feature_id = Db::getInstance()->getValue($sql);

					Db::getInstance()->execute(
						'INSERT INTO `' . _DB_PREFIX_ . 'feature_value` ( `id_feature`, `custom`)
							VALUES ('. (int) $feature_id . ', '. 0 . ')'
					);
					$sql = 'SELECT MAX(`id_feature_value`)
							FROM `' . _DB_PREFIX_ . 'feature_value`';
						$id_feature_value = Db::getInstance()->getValue($sql);
					Db::getInstance()->execute(
						'INSERT INTO `' . _DB_PREFIX_ . 'feature_value_lang` ( `id_feature_value`, `id_lang`, `value`)
							VALUES ('. (int) $id_feature_value . ', '. 1 . ', '. $customer_id . '),
									('. (int) $id_feature_value . ', '. 2 . ', '. $customer_id . ')'
					);
					echo 1;
					die();

					}else{
						echo 0;
						die();
					}
			}

			return $result;

			} catch (\Throwable $th) {
				echo $th;
				die();
			}
			
		}
	}


	public static function SplitCustomerUpdate($customer_id,$feed,$typefeed)
	{
		if($customer_id == "" && $feed =="" ){
			echo 0;
			die();
		}else{
			if($customer_id ==""){
				echo 0;
				die();
			}

			if($feed ==""){
				echo 0;
				die();
			}

		try {
			$db = Db::getInstance();
			$request = 'SELECT * FROM `'.SplitRules::$definition['table'].'`WHERE `customer_id` = "'.$customer_id.'"';
			/** @var array $result */
			$result2 = $db->executeS($request);
			$count_ =count($result2);
			if ($count_ > 0){
			
				$result_ = $db->update('payco_split', 
				array(
					'typefeed'=>$typefeed,
					'feed' => $feed
				), 'customer_id = '.$customer_id );
		
				if($result_){
					echo 1;
					die();
				}

			}else{
				echo 0;
				die();
			}

		return $result;

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
			$sql = array();

			$db = Db::getInstance();
			$result_ = $db->execute('
			DELETE FROM `'._DB_PREFIX_.'payco_split`
			WHERE id = "'.intval($id_).'"');
			$sql_ = '
				SELECT `id_feature`
				FROM `' . _DB_PREFIX_ . 'feature_lang`
				WHERE `name` = ' . '"ePayco receiver"';

			$feature_id = Db::getInstance()->getValue($sql_);

			$sqlRequest = '
					SELECT `id_feature_value`
					FROM `' . _DB_PREFIX_ . 'feature_value_lang`
					WHERE `value` = ' . $customer_id;

			$id_feature_value = Db::getInstance()->getValue($sqlRequest);

			$sql[0] = '
				DELETE
					`' . _DB_PREFIX_ . 'feature_value_lang`
				FROM
					`' . _DB_PREFIX_ . 'feature_value_lang`
					JOIN `' . _DB_PREFIX_ . 'feature_value`
						ON (`' . _DB_PREFIX_ . 'feature_value_lang`.id_feature_value = `' . _DB_PREFIX_ . 'feature_value`.id_feature_value)
				WHERE
					`' . _DB_PREFIX_ . 'feature_value`.`id_feature` = ' . (int) $feature_id . '
					AND `' . _DB_PREFIX_ . 'feature_value`.`id_feature_value` = ' . (int)$id_feature_value . '
			';
			$sql[1] = '
				DELETE FROM `' . _DB_PREFIX_ . 'feature_value`
				WHERE `id_feature` = ' . (int)$feature_id . ' AND  `id_feature_value` = '.(int)$id_feature_value;
				foreach ($sql as $query) {
					if (Db::getInstance()->execute($query) == false) {
						return false;
					}
				}
		} catch (\Throwable $th) {
			throw $th;
		}

        if($result_){
           echo "1";
        }else{
            echo "0";
        }
        exit();
		
	}


	/**
	 * Consultar si existe el registro de una oden
	 * @param int $orderId
	 */	
	public static function ifExist($orderId)
	{
		$sql = 'SELECT COUNT(*) FROM '.EpaycoOrder::$definition['table'].' WHERE order_id ='.$orderId;
		
		if (\Db::getInstance()->getValue($sql) > 0)
			return true;
		return false;
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
		$result = $db->update('payco_split', array('order_stock_discount'=>1), 'order_id = '.(int)$orderId );

		return $result ? true : false;
	}
	
	/**
	 * Crear la tabla en la base de datos.
	 * @return true or false
	 */
	public static function setup()
	{
		$sql = array();
		$sql[0] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'payco_split` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
		    `customer_id` INT(11) NULL,
		    `typefeed` TEXT NULL,
		    `feed` TEXT NULL,
		    `avtive` int(3) NULL,
		    PRIMARY KEY  (`id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        $sqlQuery = 'SELECT `position`+1
				FROM `' . _DB_PREFIX_ . 'feature`
				    ORDER BY position DESC';
        $_POST_position = Db::getInstance()->getValue($sqlQuery);
        Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'feature` ( `position`)
            VALUES ('. (int) $_POST_position . ')');
        $sqlQuery = 'SELECT MAX(`id_feature`)
				FROM `' . _DB_PREFIX_ . 'feature`';
        $feature_id = Db::getInstance()->getValue($sqlQuery);
        $sql[2] = 'INSERT INTO `' . _DB_PREFIX_ . 'feature_lang` ( `id_feature`,`id_lang`,`name`)
            VALUES ('. (int) $feature_id . ', '. 1 . ', '. '"ePayco receiver"' . '),
                   ('. (int) $feature_id . ', '. 2 . ', '. '"ePayco receiver"' . ')';
        $sql[3] = 'INSERT INTO `' . _DB_PREFIX_ . 'feature_shop` ( `id_feature`, `id_shop`)
            VALUES ('. (int) $feature_id . ','. 1 . ')';

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
		$sql = array();
        $sql[0] ='DROP TABLE IF EXISTS '._DB_PREFIX_.'payco_split';

        $sql_ = '
			SELECT `id_feature`
			FROM `' . _DB_PREFIX_ . 'feature_lang`
			WHERE `name` = ' . '"ePayco receiver"'
        ;

        $feature_id = Db::getInstance()->getValue($sql_);
        $sql[1] = '
			DELETE
				`' . _DB_PREFIX_ . 'feature_value_lang`
			FROM
				`' . _DB_PREFIX_ . 'feature_value_lang`
				JOIN `' . _DB_PREFIX_ . 'feature_value`
					ON (`' . _DB_PREFIX_ . 'feature_value_lang`.id_feature_value = `' . _DB_PREFIX_ . 'feature_value`.id_feature_value)
			WHERE
				`' . _DB_PREFIX_ . 'feature_value`.`id_feature` = ' . (int) $feature_id . '
		';
        $sql[2] = '
			DELETE FROM `' . _DB_PREFIX_ . 'feature_value`
			WHERE `id_feature` = ' . (int)$feature_id;
        $sql[3] = '
			DELETE FROM `' . _DB_PREFIX_ . 'feature_shop`
			WHERE `id_feature` = ' . (int)$feature_id;
        $sql[4] = '
			DELETE FROM `' . _DB_PREFIX_ . 'feature`
			WHERE `id_feature` = ' . (int)$feature_id;
        $sql[5] = '
			DELETE FROM `' . _DB_PREFIX_ . 'feature_lang`
			WHERE `id_feature` = ' . (int)$feature_id;

		foreach ($sql as $query) {
		    if (Db::getInstance()->execute($query) == false) {
		        return false;
		    }
		}
	}
}