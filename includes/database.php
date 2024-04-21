<?php
/**
 +-------------------------------------------------------------------------+
 | Rubify  - An MiniDLNA Webapp                                            |
 | Version 1.0.0                                                           |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License as published by    |
 | the Free Software Foundation.                                           |
 |                                                                         |
 | This file forms part of the Rubify software.                            |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Rubify Software, you               |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Jaime Rubio <jaime@rubiogafsi.com>                              |
 +-------------------------------------------------------------------------+
*/
namespace Rubify\Framework;

defined('_RBFYEXEC') or die;

class Database{

    protected $database;
    protected $filedb;
    protected $connection;
    protected $sql;
    protected $nameQuote;

	public function __construct( $filedb)
	{
            // Set class options.
            $this->filedb = $filedb;

            $this->connect();
	}

	public function __destruct()
	{
		$this->disconnect();
	}


    public function connect() {

        if ($this->connection)        
		    return;            

		$this->connection = new \SQLite3($this->filedb ,SQLITE3_OPEN_READONLY);        
    }

    function query( $sql = '')
    {
        if($sql != '') {
            $this->sql = $sql;
        }

        return $this->connection->query($this->sql);
    }

	function escape($text, $extra = false)
	{
		$this->connect();

		$result = \SQLite3::escapeString($text);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

    function loadRows ($sql = '')
    {
        if($sql != '') {
            $this->sql = $sql;
        }

        $rows = [];
        if ($result = $this->query())
        {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    function loadRow ($sql = '')
    {
        if($sql != '') {
            $this->sql = $sql;
        }

        if ($result = $this->query())
        {
            return $result->fetchArray(SQLITE3_ASSOC);
        }
		return false;
    }	

    function getNumRows( $sql = '')
    {
        if($sql != '') {
            $this->sql = $sql;
        }

        $result = $this->query($sql);
        $count = 0;
        while($rows = $result->fetchArray(SQLITE3_ASSOC)){
            ++$count;
        }
        return $count;     
    }

    function loadResult( $sql ='')
    {
        if($sql != '') {
            $this->sql = $sql;
        }

	    if (!($cur = $this->query())) {
            return null;
	    }
	
        $ret = null;
	    if ($row = $cur->fetchArray()) {
            $ret = $row[0];
	    }    
	    return $ret;
    }

    function loadResultArray($numinarray = 0)
    {
	    if (!($cur = $this->query())) {
            return null;
	    }
	
        $array = array();
	    while ($row = $cur->fetchArray()) {
            $array[] = $row[$numinarray];
	    }
	    return $array;
    }

    public function disconnect(){
        $this->connection->close();
    }

    public function quoteName($name, $as = null)
	{
		if (\is_string($name))
		{
			$name = $this->quoteNameString($name);

			if ($as !== null)
			{
				$name .= ' AS ' . $this->quoteNameString($as, true);
			}

			return $name;
		}

		$fin = [];

		if ($as === null)
		{
			foreach ($name as $str)
			{
				$fin[] = $this->quoteName($str);
			}
		}
		elseif (\is_array($name) && (\count($name) === \count($as)))
		{
			$count = \count($name);

			for ($i = 0; $i < $count; $i++)
			{
				$fin[] = $this->quoteName($name[$i], $as[$i]);
			}
		}

		return $fin;
	}

	protected function quoteNameString($name, $asSinglePart = false)
	{
		$q = $this->nameQuote . $this->nameQuote;

		// Double quote reserved keyword
		$name = str_replace($q[1], $q[1] . $q[1], $name);

		if ($asSinglePart)
		{
			return $q[0] . $name . $q[1];
		}

		return $q[0] . str_replace('.', "$q[1].$q[0]", $name) . $q[1];
	}

}
