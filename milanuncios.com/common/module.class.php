<?php


class module
{
	protected $config;
	protected $configApp;
	protected $useragent = 'Mozilla/5.0 (Linux; U; Android 5.0.1; de-ch; HTC Sensation Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
	
	public $log = array(
		'all_processed_ad' => 0,
		'added' => 0,	
		'already_added' => 0,	
		'error' => 0
	);

	
	public function getXpathObjs($path, $pageList, $getValue=true, $attr=false){		
		$dom = new DomDocument();
		libxml_use_internal_errors(false);
		@$dom->loadHTML($pageList);
		$xpath = new DomXPath($dom);
		$xpathObjs = $xpath->query($path);
		
		if($getValue){	
			$result = array();	
			foreach($xpathObjs as $obj){
				if($attr){
					$result[] = $obj->getAttribute($attr);
				}else{
					$result[] = $obj->nodeValue;
				}
			}		
			if(count($result) === 1){
				$result = trim($result[0]);
			}
			elseif(count($result) === 0){
				$result = '';
			}
			$xpathObjs = $result;
		}
		
		return $xpathObjs;
	}


	public function getPages($adUrls){
		$adPages = array();
		if(!$adUrls) 
			return $adPages;
		if($this->config['threads'] > 1){
			$rollingCurl = new RollingCurl\RollingCurl();
			foreach($adUrls as $adUrl){
				$rollingCurl->get($adUrl);
			}
			$rollingCurl->setCallback(function(RollingCurl\Request $request, RollingCurl\RollingCurl $rollingCurl) use (&$adPages) {
				$adPages[$request->getUrl()] = $request->getResponseText();
				$this->requests++;	
				$info = $request->getResponseInfo();
				echo($info["http_code"]." ".$request->getUrl());
			})
			->setSimultaneousLimit($this->config['threads'])
			->execute();
		}else{
			foreach($adUrls as $adUrl){
				$adPages[$adUrl] = $this->getPage($adUrl);
			}
		}
		
		return $adPages;
	}


	public function getPage($url, $opts = array()){
		$page = false;
		$curl = curl_init();
		$opts[CURLOPT_URL] = $url;
		$opts[CURLOPT_RETURNTRANSFER] = true;
		$opts[CURLOPT_FOLLOWLOCATION] = true;			
		$opts[CURLOPT_USERAGENT] = $this->useragent;

		curl_setopt_array($curl, $opts);
		$data = curl_exec($curl);
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		//print curl_error($curl);
		curl_close($curl);
		echo($code." ".$url."\n");
		if($code === 200)
		{
			$page = $data;
		}	
		elseif($code === 403)
		{
			echo('403 ban');
		}
		elseif($code === 401)
		{
			return false;
		}
		return $page;
	}
	
	
	protected function saveImg($ad){
		$saveImages = array();	
		if( $imgFiles = $this->getPages($ad['images']) ){
			$i = 1;
			foreach($imgFiles as $url => $imgFile){
				$name = $ad['unique_id'].'_'.$i.'.jpg';
				if( file_put_contents($this->configApp['imagesPath'].'/'.$name, $imgFile) ){
					$saveImages[] = $name;
					$i++;
				}
			}		
		}

		return $saveImages;
	}
	
	
	protected function getConfig($moduleName=false){
		require __DIR__ . '/../config.php';
		
		if($moduleName){
			return $config['module'][$moduleName];
		}else{
			return $config;
		}
	}

	
}
