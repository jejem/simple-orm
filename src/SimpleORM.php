<?php
/**
 * ORM/SimpleORM.php
 *
 * @author Jérémy 'Jejem' Desvages <jejem@phyrexia.org>
 * @copyright Jérémy 'Jejem' Desvages
 * @license The MIT License (MIT)
 * @version 1.3.0
**/

namespace Phyrexia\ORM;

use Phyrexia\SQL\SimpleSQL;

abstract class SimpleORM {
	protected static $table;
	public static $mc;

	public static function load($id) {
		$that = get_called_class();

		if (isset(self::$mc) && is_object(self::$mc) && get_class(self::$mc) == 'Memcached') {
			$ret = self::$mc->get($that.'_'.$that::$table.'_'.$id);

			if ($ret && is_object($ret) && get_class($ret) == $that)
				return $ret;
		}

		$SQL = SimpleSQL::getInstance();

		$SQL->doQuery('SELECT * FROM @1 WHERE @2 = %3 LIMIT 1', $that::$table, 'id', $id);
		if ($SQL->numRows() == 0)
			return false;

		$row = $SQL->fetchResult();

		$object = new $that();
		foreach ($row as $k => $v) {
			if ((substr($v, 0, 1) == '[' && substr($v, -1) == ']') || (substr($v, 0, 1) == '{' && substr($v, -1) == '}')) {
				$buf = json_decode($v, true);
				if (is_array($buf))
					$v = $buf;
			}

			$object->$k = $v;
		}

		if (isset(self::$mc) && is_object(self::$mc) && get_class(self::$mc) == 'Memcached')
			self::$mc->add($that.'_'.$that::$table.'_'.$id, $object);

		return $object;
	}

	public function save() {
		$that = get_called_class();

		$SQL = SimpleSQL::getInstance();

		if (is_null($this->id) || ! $that::exists($this->id)) {
			$SQL->doQuery('INSERT INTO @1 (@2) VALUES (%3)', $that::$table, 'id', ((! is_null($this->id))?$this->id:''));
			if (is_null($this->id))
				$this->id = $SQL->insertID();
		}

		$SQL->doQuery('SHOW COLUMNS FROM @1', $that::$table);
		$rows = $SQL->fetchAllResults();

		$elements = array();
		foreach ($rows as $row) {
			$k = $row['Field'];
			$v = $this->$k;

			if (is_array($v)) {
				$buf = json_encode($v);
				if ($buf !== false)
					$v = $buf;
			}

			$elements[] = '`'.mysqli_real_escape_string($SQL->getLink(), $k).'`="'.mysqli_real_escape_string($SQL->getLink(), $v).'"';
		}

		$SQL->doQuery('UPDATE @1 SET '.implode(',', $elements).' WHERE @2 = %3 LIMIT 1', $that::$table, 'id', $this->id);

		if (isset(self::$mc) && is_object(self::$mc) && get_class(self::$mc) == 'Memcached')
			self::$mc->set($that.'_'.$that::$table.'_'.$this->id, $this);

		return $this;
	}

	public static function exists($id) {
		$that = get_called_class();

		$SQL = SimpleSQL::getInstance();

		$SQL->doQuery('SELECT 1 FROM @1 WHERE @2 = %3 LIMIT 1', $that::$table, 'id', $id);
		if ($SQL->numRows() != 1)
			return false;

		return true;
	}

	public function delete() {
		$that = get_called_class();

		if (! $that::exists($this->id))
			return false;

		$SQL = SimpleSQL::getInstance();

		$SQL->doQuery('DELETE FROM @1 WHERE @2 = %3 LIMIT 1', $that::$table, 'id', $this->id);

		if (isset(self::$mc) && is_object(self::$mc) && get_class(self::$mc) == 'Memcached')
			self::$mc->delete($that.'_'.$that::$table.'_'.$this->id);

		return true;
	}
}
