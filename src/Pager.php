<?php
namespace gsoft;


class Pager
{
	/**
	 * How many records must be displayed per page.
	 */
	const ITEMS_IN_PAGE = 5;
	
	function __construct()
	{
	}
	
	public static function getQueries($input, $entriesCount)
	{
		$queries = array();
		//число страниц, на которых можно отобразить все  результаты
		$pageCount = ceil($entriesCount / self::ITEMS_IN_PAGE);
		//для каждой из страниц создаём оригинальную ссылку
		for ($i = 1; $i <= $pageCount; $i++) {
			$queries[$i] = self::getPaginationQuery($input, $i);
		}
		
		return $queries;
	}
	
	/**
	 * Fetches current get parameter and produces query for them. Just append it to url with '?'.
	 * @param array $input Array, containing GET keys=>values
	 * @param int $page Current page
	 * @return string query to append to URL
	 */
	private static function getPaginationQuery($input, $page = 0)
	{
		if (is_int($page)) {
			$input['page'] = $page;
		} else throw new \InvalidArgumentException('Parameter is not int.');
		
		//сортируем прочие get параметры
		ksort($input);
		//строим строку параметров для будущих urlов
		$query = http_build_query($input);
		
		return $query;
	}
	
}