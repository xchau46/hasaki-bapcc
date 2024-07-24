<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Search extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('frontend/Mcontent');
		$this->load->model('frontend/Mcategory');
		$this->load->model("frontend/Mproduct");
		
		$this->load->model('backend/Muser');
		$this->data['com'] = 'search';
	}
	public function index()
	{
		$this->load->library('phantrang');
		$key = $_GET['search'];
		$key1 = $this->Muser->blocksqlinjection($key);

		$aurl = explode('/', uri_string());
		$url = $aurl[0] . '?search=' . str_replace(' ', '+', $key1);
		$limit = 10;
		$current = $this->phantrang->PageCurrent();
		$first = $this->phantrang->PageFirst($limit, $current);
		$total = $this->Mproduct->product_search_count($key1);
		$this->data['list'] = $this->Mproduct->product_search($key1, $limit, $first);;
		$this->data['strphantrang'] = $this->phantrang->PagePer($total, $current, $limit, $url = $url);
		$this->data['title'] = 'Chau Dien Hasaki- Bạn muốn tìm gì ?';
		$this->data['view'] = 'index';
		$this->data['count'] = $total;
		$this->data['key'] = $key1;
		$this->load->view('frontend/layout', $this->data);
	}
}
