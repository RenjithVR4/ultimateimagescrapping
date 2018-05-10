<?php 


libxml_use_internal_errors(true);

function image_scrapper($url)
{
	$url = addhttp($url);
	$parse_url = parse_url($url);

	if(isset($parse_url['scheme']))
	{
		$domain_address = $parse_url['scheme'];
	}

	if(isset($parse_url['host']))
	{
		$domain_address = $parse_url['host'];
	}

    $imageData = array();
	$imageNames_regex = '/[\w-]+.(jpg|png|jpeg|gif|bmp)/';
	
	$content = file_get_contents($url);

	if($content === false)
	{
		$imageData = image_scrapper_sub($url);
		return $imageData;
	}
	else 
	{
		$doc = new DOMDocument(); 
	    $doc->loadHTML($content);
	    $doc->preserveWhiteSpace = false;
	    $imgElements = $doc->getElementsByTagName('img');

	    $images = array();

	    for($i = 0; $i < $imgElements->length; $i++)
	    {
	        if($imgElements->item($i)->getAttribute('src'))
	        {
	        	$images[] = $imgElements->item($i)->getAttribute('src');
	        }
	        else if($imgElements->item($i)->getAttribute('data-img-src'))
	        {
	        	$images[] = $imgElements->item($i)->getAttribute('data-img-src');
	        }
	    }

		foreach ($images as $image)
		{
			$parse = parse_url($image);

			$image = addDomain($image, $domain_address);

			preg_match($imageNames_regex, $image, $matches);

			$imageName = basename($image);

			if(isset($parse['host']))
			{
			    if(isset($imageName))
			    {
			    	file_put_contents('images/'.$imageName, file_get_contents($image));
			    }
			    else
			    {
			    	$image = addDomain($image, $domain_address);
			    	
			    	$name = substr(md5(mt_rand()), 0, 7);

			    	file_put_contents('images/'.$name, file_get_contents($image));
			    }
			}
			else
			{
				$image = addDomain($image, $domain_address);

				if(checkRemoteFile($image) && isImage($image))
				{
					if(isset($imageName))
				    {
			    		if(file_put_contents('images/'.$imageName, file_get_contents($image)) === false)
			    		{
			    			if (file_exists('images/'.$imageName)) 
		    			 	{ 
		    			 		unlink('images/'.$imageName); 
		    			 	}
			    		}
				    }
				    else
				    {
				    	$name = substr(md5(mt_rand()), 0, 7);
				    	if(file_put_contents('images/'.$name, file_get_contents($image)) === false)
			    		{
			    			if (file_exists('images/'.$name))
			    			{ 
			    			  unlink('images/'.$name); 
			    			}
			    		}
				    	
				    }
				}
				
			}

		   
		    $imageData[] = $image;
		}


		$imageData = array_unique($imageData);

		if(count($imageData) == 0)
		{
			return array("Empty" => "No Images Found!");
		}

	    return $imageData;
	}

	
}


function image_scrapper_sub($url)
{

	$parse_url = parse_url($url);

	if(isset($parse_url['scheme']))
	{
		$domain_address = $parse_url['scheme'];
	}

	if(isset($parse_url['host']))
	{
		$domain_address = $parse_url['host'];
	}


    $headers[]  = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; 
        rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13";
    $headers[]  = "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,
        */*;q=0.8";
    $headers[]  = "Accept-Language:en-us,en;q=0.5";
    $headers[]  = "Accept-Encoding:gzip,deflate";
    $headers[]  = "Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $headers[]  = "Keep-Alive:115";
    $headers[]  = "Connection:keep-alive";
    $headers[]  = "Cache-Control:max-age=0";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_ENCODING, "gzip");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_COOKIESESSION, true );
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt' );
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt' );
    curl_setopt($curl, CURLOPT_MAXREDIRS, 1);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);

    $data = curl_exec( $curl );
    $header = curl_getinfo( $curl );

    $imageData = array();
	$imageNames_regex = '/[\w-]+.(jpg|png|jpeg|gif)/';
	$getallimages_regex = '/([-a-z0-9_\/:.]+\.(jpg|jpeg|png|gif|bmp))/i';
	$images = array();

	if (preg_match_all($getallimages_regex, $data, $images, PREG_SET_ORDER))
	{
		
		foreach ($images as $image)
		{

			$image = addDomain($image, $domain_address);

			$parse = parse_url($image);
			preg_match($imageNames_regex, $image, $matches);

			if(isset($parse['host']))
			{
			    if(isset($matches[0]))
			    {
			    	if(file_put_contents('images/'.$matches[0], file_get_contents($image)) === false)
			    	{
			    		unset($image);
			    	
			    		if (file_exists('images/'.$matches[0]))
			    		{   
							unlink('images/'.$matches[0]);                     
						}
			    	}
			    }
			    else
			    {
			    	$name = substr(md5(mt_rand()), 0, 7);
			    	if(file_put_contents('images/'.$name, file_get_contents($image)) === false)
			    	{
			    		unset($image);

			    		if (file_exists('images/'.$matches[0]))
			    		{   
							unlink('images/'.$matches[0]);                     
						}
			    	}
			    }
			}
			else
			{
				$image = addDomain($image, $domain_address);

				if(isset($matches[0]))
			    {
			    	if(file_put_contents('images/'.$matches[0], file_get_contents($image)) === false)
			    	{
			    		unset($image);
			    		
			    		if (file_exists('images/'.$matches[0]))
			    		{   
							unlink('images/'.$matches[0]);                     
						}
			    	}
			    }
			    else
			    {
			    	$name = substr(md5(mt_rand()), 0, 7);

			    	if(file_put_contents('images/'.$name, file_get_contents($image)) === false)
			    	{
			    		unset($image);

			    		if (file_exists('images/'.$matches[0]))
			    		{   
							unlink('images/'.$matches[0]);                     
						}
			    	}
			    }
			}

		   
		    $imageData[] = $image;
		}


		$imageData = array_unique($imageData);

		if(count($imageData) == 0)
		{
			return array("Empty" => "No Images Found!");
		}

	    return $imageData;

	}
	
  
}

function addhttp($url)
{
	$urlParts = parse_url($url);
	
	if ((!preg_match("~^(?:f|ht)tp?://~i", $url)) && (!preg_match("~^(?:f|ht)tps?://~i", $url)))
    {
        $url = "http://" . $url;
    }
	
    
    return $url;
}


function addDomain($url, $domain)
{

	$parse_url = parse_url($url);

	if(!isset($parse_url['host']))
	{
		if (strpos($url, '/') === 0 )
		{
		 	$url = ltrim($url, '/');
		}
		

		$url =  'http://'. $domain . '/' . $url;
	}
	else if(!isset($parse_url['scheme']))
	{
		if (strpos($url, '/') === 0 )
		{
		 	$url = ltrim($url, '/');
		}

		$url = 'http://' . $url;
	}

	return $url;
}


function checkRemoteFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);
    curl_close($ch);
    if($result !== FALSE)
    {
        return true;
    }
    else
    {
        return false;
    }
}


function isImage($url)
{
    return preg_match("/^[^\?]+\.(jpg|jpeg|gif|png)(?:\?|$)/", $url);
}


$data = image_scrapper( "http://www.dailymail.co.uk/home/index.html" );

libxml_use_internal_errors(false);

echo '<pre>';
print_r($data);
echo "</pre>";


 ?>