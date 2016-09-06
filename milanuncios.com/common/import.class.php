<?php


class import
{
	
	public $db;	
	private $config;
	
	function __construct(){	
		$this->config = require __DIR__ . '/../config.php';	
		$this->db = db::getInstance();			
	}


	public function add($ad){
		$add = false;
		
		/* format ad*/
		if( !($ad = $this->formatAd($ad)) )
			return false;
		/**/
		
		var_dump($ad);
		$query = sprintf(
			'INSERT INTO posts (%s) VALUES ("%s")',
			implode(',',array_keys($ad)),
			implode('","', array_values($ad))
		);
		echo $query;
		/*$query = $this->db->query($query);
		
		if($query){
			echo("<-add");
			$add = true;
		}else{
			echo("Error add: ". $this->db->error);
		}*/
		
		return $add;
	}
	
	
	protected function getCategoryId($category){
		$allcategory = [
			'inmobiliaria' => 7,
			'motor' => 8,
			'negocios' => 158,
			'comunidad' => 2,
			'empleo' => 4,
			'moda' => 3,
			'juegos' => 194,
			'aficiones' => 216,
			'informatica' => 1,
			'casajardin' => 206,
			'mascotas' => 6,
			'servicios' => 9,
			'imagensonido' => 168,
			'deportes' => 228,
			'telefonia' => 179,
			'formacion' => 148,
		];
		
		if(!empty($allcategory[$category])){
			return $allcategory[$category];
		}else{
			return false;
		}
	}
	
	
	public function formatAd($ad){
		
		$fad = array();
		
		$fad['unique_id'] = $ad['unique_id'];

		if(!empty($ad['title'])){
			$fad['title'] = '{"en":"'.$ad['title'].'","es":"'.$ad['title'].'","ru":"'.$ad['title'].'"}';
		}
	
		if(!empty($ad['description'])){
			$fad['description'] = $ad['description'];
		}
		
		$fad['purpose'] = 'sell';
		if(!empty($ad['rs_type'])){
			$fad['purpose'] = $ad['rs_type'];
		}
		
		if(!empty($ad['category'])){
			$fad['category'] = $this->getCategoryId($ad['category']);
		}
		
		if(!empty($ad['price'])){
			$ad['price'] = preg_replace('/[^0-9\,]+/', '', $ad['price']);
			$fad['price'] = $ad['price'];
		}
	
		if(!empty($ad['city'])){
			$fad['address'] = $ad['city'];
		}
		
		if(!empty($ad['phone_no'])){
			$fad['phone_no'] = implode(", ", $ad['phone_no']);
		}
	
		$fad['country'] = 2;

		if(!empty($ad['city'])){
			var_dump($ad['city']);
			$ad['city'] = $this->getCityId($ad['city']);
			var_dump($ad['city']);
			if($ad['city'])
				$fad['city'] = $this->getCityId($ad['city']);
		}		
		
		if(!empty($ad['address'])){
			$ad['address'] = json_decode($ad['address']);
			if($ad['address']){
				$fad['longitude'] = $ad['address']['lng'];
				$fad['latitude'] = $ad['address']['lat'];
			}	
		}

		if(!empty($ad['images'])){
			$fad['featured_img'] = array_shift($ad['images']);
			$fad['gallery'] = json_encode($ad['images']);
		}
		
		$fad['created_by'] = 2;
		$time = time();
		if(!empty($ad['date'])){
			$time = strtotime($ad['date'].'+01:00');
		}
		$fad['create_time'] = $time;
		$fad['publish_time'] = $time;
		$fad['status'] = 1;
		
		if(!empty($ad['m2'])){
			$fad['dop_est_area'] = $ad['m2'];
		}
		
		//dop_est_floor
		
		if(!empty($ad['dor'])){
			$ad['dor'] = preg_replace('/[^0-9\,]+/', '', $ad['dor']);
			$fad['dop_est_rooms'] = $ad['dor'];
		}
		
		if(!empty($ad['ban'])){
			$ad['ban'] = preg_replace('/[^0-9\,]+/', '', $ad['ban']);
			$fad['dop_est_baths'] = $ad['ban'];
		}
		
		if(!empty($ad['ano'])){
			$ad['ano'] = preg_replace('/[^0-9\,]+/', '', $ad['ano']);
			$fad['dop_car_year'] = $ad['ano'];
		}
		
		if(!empty($ad['kms'])){
			$fad['dop_car_mileage'] = $ad['kms'];
		}	
			
		if(!empty($ad['die'])){
			$fad['dop_car_diesel'] = 1;
		}	
		
		if(!empty($ad['cc'])){
			$fad['dop_car_volume'] = $ad['cc'];
		}
		
		foreach($fad as &$value)
			$value = mysql_real_escape_string($value);
			
		return $fad;	
	}


	public function getCityId($city){
		$id = false;
		$query = "SELECT id FROM locations WHERE `name`='{$city}' and `type`='city'";
		$query = $this->db->query($query);
		if(!$query) {
			echo("\nError getCityId city: ". $this->db->error);
		}
		if(mysqli_num_rows($query) > 0){
			$row = $query->fetch_assoc();
			$id = $row['id'];
		}else{
			$query = "INSERT INTO `locations` (parent, parent_country, name, type) VALUES (2, 2, '{$city}', 'city')";
			$query = $this->db->query($query);
			if($query){
				$id = $this->db->insert_id;
			}else{
				echo("\nError getCityId city: ". $this->db->error);				
			}
		}
		
		return $id;		
	}


	public function exist($unique_id){
		$exist = false;
		$query = "SELECT `unique_id` FROM `posts` WHERE `unique_id`='{$unique_id}'";
		$query = $this->db->query($query);
			
		if(!$query) {
			echo("\nError Exist: ". $this->db->error);
		}
		
		if(mysqli_num_rows($query) > 0){
			$exist = true;
		}else{
			echo $unique_id." \n";	
		}
	
		return $exist;
	}
	
	
	

}
