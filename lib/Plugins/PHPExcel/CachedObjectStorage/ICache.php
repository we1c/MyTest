<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    Plugins_PHPExcel_CachedObjectStorage
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.8.0, 2014-03-02
 */


/**
 * Plugins_PHPExcel_CachedObjectStorage_ICache
 *
 * @category   PHPExcel
 * @package    Plugins_PHPExcel_CachedObjectStorage
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
interface Plugins_PHPExcel_CachedObjectStorage_ICache
{
    /**
     * Add or Update a cell in cache identified by coordinate address
     *
     * @param	string			$pCoord		Coordinate address of the cell to update
     * @param	Plugins_PHPExcel_Cell	$cell		Cell to update
	 * @return	void
     * @throws	Plugins_PHPExcel_Exception
     */
	public function addCacheData($pCoord, Plugins_PHPExcel_Cell $cell);

    /**
     * Add or Update a cell in cache
     *
     * @param	Plugins_PHPExcel_Cell	$cell		Cell to update
	 * @return	void
     * @throws	Plugins_PHPExcel_Exception
     */
	public function updateCacheData(Plugins_PHPExcel_Cell $cell);

    /**
     * Fetch a cell from cache identified by coordinate address
     *
     * @param	string			$pCoord		Coordinate address of the cell to retrieve
     * @return Plugins_PHPExcel_Cell 	Cell that was found, or null if not found
     * @throws	Plugins_PHPExcel_Exception
     */
	public function getCacheData($pCoord);

    /**
     * Delete a cell in cache identified by coordinate address
     *
     * @param	string			$pCoord		Coordinate address of the cell to delete
     * @throws	Plugins_PHPExcel_Exception
     */
	public function deleteCacheData($pCoord);

	/**
	 * Is a value set in the current Plugins_PHPExcel_CachedObjectStorage_ICache for an indexed cell?
	 *
	 * @param	string		$pCoord		Coordinate address of the cell to check
	 * @return	boolean
	 */
	public function isDataSet($pCoord);

	/**
	 * Get a list of all cell addresses currently held in cache
	 *
	 * @return	array of string
	 */
	public function getCellList();

	/**
	 * Get the list of all cell addresses currently held in cache sorted by column and row
	 *
	 * @return	void
	 */
	public function getSortedCellList();

	/**
	 * Clone the cell collection
	 *
	 * @param	Plugins_PHPExcel_Worksheet	$parent		The new worksheet
	 * @return	void
	 */
	public function copyCellCollection(Plugins_PHPExcel_Worksheet $parent);

	/**
	 * Identify whether the caching method is currently available
	 * Some methods are dependent on the availability of certain extensions being enabled in the PHP build
	 *
	 * @return	boolean
	 */
	public static function cacheMethodIsAvailable();

}
