<?php

class milanuncios extends module
{
	
	function __construct(){
		
		$this->configApp = require __DIR__ . '/../config.php';
		$this->config = $this->configApp['module'][get_class($this)];
		
		$this->log['date'] = date("d-m-Y H:i:s");
		$this->log['time'] = microtime(true);
	}
	
		
	public function run($thisName = 'milanuncios'){
		$config = $this->config;
		$this->log['module'] = $thisName;
		$import = new import;
		
		$limit = $this->config['limit'];
		$is_limit = true;
		if($limit === 0){
			$is_limit = false;
		}

		
		foreach($config['categories'] as $cat_name => $cat_url){
			$data = true;
			$i = 1;
			while($data){
				
				if($pageList = $this->getpage($config['url'].$cat_url.'&pagina='.$i)){
					$adPages = array();
						
					if( $urls = $this->getAdUrls($pageList) ){
						$adUrls = array();
						foreach($urls as $url){
							$this->log['all_processed_ad']++;
							if( $import->exist($url) ){
								$this->log['already_added']++;
							}else{
								if($is_limit){
									if($limit>0){
										$limit--;
									}else{
										$this->log['all_processed_ad']--;									
										$data = false;
										break;
									}
								}
								$adUrls[] = $url;
							}
						}
						$urls = $adUrls;
						$adPages = $this->getPages($urls);
					}
								
					if($adPages){
						foreach($adPages as $url => $page){
							if($ad = $this->getAd($page, $url)){
								if($import->add($ad)){
									$this->log['added']++;
								}else{ 
									$this->log['error']++;
								}
							}else{
								$data = false;
							}						
						}
					}	
											
					$i++;
				}else{
					$data = false;
				}
				
			}
		}
		$this->log['time'] = microtime(true) - $this->log['time'];
	}
	
	public function getAdUrls($page){
		$adUrls = array();
		$xpath = '//*[@id="cuerpo"]/div/div[2]/a';
		$xpathObjs = $this->getXpathObjs($xpath, $page, false);
		foreach($xpathObjs as $obj){
			$href = $obj->getAttribute('href');
			if($href[0]==='/'){
				$adUrls[] = $this->config['url'].$href;
			}
		}
		return $adUrls;
	}


	public function getAd($page, $url){
		$page = str_replace("\n", "", $page);
		$ad = array();
		$ad['url'] = $url;
		$ad['unique_id'] = md5($ad['url']);
		
		$ad['images'] = $this->getXpathObjs('//div[contains(@class, "pagAnuFoto")]/img', $page, true, 'src');
		if($ad['images']){
			if(!is_array($ad['images']))
				$ad['images'] = array($ad['images']);
			$ad['images'] = $this->saveImg($ad);
		}
		$page = mb_convert_encoding($page, 'HTML-ENTITIES', 'utf-8');
				
		preg_match_all("/([0-9]{3,})\.htm$/", $ad['url'], $id);
		$ad['id'] = ($id[1]) ? $id[1][0] : '';
		if($ad['id']){
			$ad['address'] = $this->getAdress($ad['id']);
			
			$ad['phone_no'] = $this->getPhone($ad['id']);			
		}

		$ad['category'] = $this->getCategory($page);
		if($ad['category'] and $ad['category']==='inmobiliaria'){	
			$xpath = '//div[contains(@class, "anuTitulo")]/b[1]/a';
			$ad['rs_type'] = $this->getXpathObjs($xpath, $page);
			if (preg_match("/Venta/i", $ad['rs_type'])){
				$ad['rs_type'] = 'sell';
			}elseif(preg_match("/Alquiler/i", $ad['rs_type'])){
				$ad['rs_type'] = 'rent';
			}/*elseif(preg_match("/Compartir/i", $ad['rs_type'])){
				$ad['rs_type'] = 'dol';
			}*/else{
				unset($ad['rs_type']);
			}
		}
		
		$ad['city'] = $this->getXpathObjs('//div[contains(@class, "anuTitulo")]/b[2]', $page);
		if($ad['city']){
			$ad['city'] = preg_replace("/[\(\)]+/", '', $ad['city']);
			$ad['city'] = str_replace("_", " ", $ad['city']);
			$ad['city'] = strtolower($ad['city']);
			$ad['city'] = ucfirst($ad['city']);
		}
		
		$xpath = '//*[@id="cuerpo"]/div[contains(@class, "pagAnuRefBox")]/div[contains(@class, "anuFecha")]';
		$ad['date'] = $this->getXpathObjs($xpath, $page);
		
		$xpath = '//*[@id="cuerpo"]/div[contains(@class, "pagAnuTituloBox")]/strong/div/a';
		$ad['title'] = $this->getXpathObjs($xpath , $page);
		
		$xpath = '//*[@id="cuerpo"]/div[contains(@class, "pagAnuDatosBox")]/div[1]';
		$ad['description'] = $this->getXpathObjs($xpath, $page);
		
		$ad['price'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "pr")]', $page);
		$ad['ano'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "ano")]', $page);
		$ad['kms'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "kms")]', $page);
		$ad['die'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "die")]', $page);
		$ad['ejes'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "ejes")]', $page);
		$ad['cc'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "cc")]', $page);	
		$ad['m2'] = $this->getXpathObjs('//div[contains(@class, "m2")]', $page);
		if($ad['m2'] && is_array($ad['m2']))
			$ad['m2'] = $ad['m2'][0];
		$ad['dor'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "dor")]', $page);
		$ad['ban'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "ban")]', $page);
		$ad['pm2'] = $this->getXpathObjs('//*[@id="pagAnuAttsAnuBox"]/div[contains(@class, "pm2")]', $page);
				
		//var_dump($ad);		
		return $ad;
	}

	
	public function getCategory($page){
		preg_match_all("/_gaq\.push\(\[._setCustomVar...1...seccion...\'(.+?)\'..3\]\)/", $page, $category);
		$category = ($category[1]) ? $category[1][0] : '';
		
		return $category;
	}
	

	public function getAdress($id){
		$adress = '';
		$pageAdress = $this->getPage('http://www.milanuncios.com/mapa/?id='.$id);
		if($pageAdress){
			$mapjs = $this->getXpathObjs('/html/body/div/script', $pageAdress);
			preg_match_all("/(\{lat..[0-9\.\-]+..lng..[0-9\.\-]+\})/", $mapjs,$adress);
			$adress = ($adress[1]) ? $adress[1][0] : '';
		}			
		return $adress;
	}

	
	public function getPhone($adcontacts){
		$opts = array(
			CURLOPT_HTTPHEADER => array("X-Requested-With: XMLHttpRequest"),
		);
		$adcontacts = 'http://www.milanuncios.com/datos-contacto/?id='.$adcontacts;
		$page = $this->getPage($adcontacts, $opts);
		if($page){
			$adcontacts = $this->getXpathObjs('/html/body/div/div/div[2]/script', $page, false);
			if($adcontacts){
				$contacts = array();
				foreach($adcontacts as $adcontact){
					$adcontacts = str_replace('"', '', $adcontact->nodeValue);
					$adcontacts = str_replace('%', '\\', $adcontacts);
					$adcontacts = json_decode('"'.$adcontacts.'"');
					preg_match_all("/tel:([0-9]{3,})/", $adcontacts, $tel);
					$contacts[] = ($tel[1]) ? $tel[1][0] : '';
				}
				$adcontacts = $contacts;
			}
		}		
		return $adcontacts;
	}

}



