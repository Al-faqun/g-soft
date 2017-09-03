<?php
namespace gsoft\Input;

use gsoft\Pager;

class SearchQueryValidator
{
    /**
     *How many items displays one page.
     */
    const ITEMS_IN_PAGE = Pager::ITEMS_IN_PAGE;
    
    /**
     * @var array Contains (or not) values of various search fields, that must be checked.
     */
    private $input;
    
    /**
     * SearchQueryValidator constructor.
     */
    public function __construct()
    {
    }
    
    /**
     * Checks provided earlier input array for specific field
     * @param array $input $_GET, $_POST, or alternative
     * @param $offset (by reference)
     * @param $limit (by reference)
     */
    public function checkPage($input, &$offset, &$limit)
    {
        $this->input = $input;
        if ( (array_key_exists('page', $this->input)) && ((int)$this->input['page'] > 0) ) {
            $pagenum = (int)$this->input['page'];
            $offset = ($pagenum - 1) * self::ITEMS_IN_PAGE;
            $limit = self::ITEMS_IN_PAGE;
        } else {
            $offset = 0;
            $limit = self::ITEMS_IN_PAGE;
        }
    }
}
