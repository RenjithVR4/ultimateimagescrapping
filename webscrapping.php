<?php 

header("Cache-Control:  maxage=1");
header("Pragma: public");
header("Content-Transfer-Encoding: Binary");


function image_scrapper($url)
{

	$parse_url = parse_url($url);
	$domain_address = "";

	if($parse_url['scheme'])
	{
		$domain_address .= $parse_url['scheme'];
	}

	if($parse_url['host'])
	{
		$domain_address .= "://".$parse_url['host'];
	}

	$domain_address .= '/';

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
	$imageurl = "";

	if (preg_match_all($getallimages_regex, $data, $images, PREG_SET_ORDER))
	{
		
		foreach ($images as $image)
		{
			if(!preg_match_all("~^(?:f|ht)tps?://~i", trim($image[0])))
		    {
		    	$parse_image_url = parse_url(trim($image[0]), PHP_URL_HOST);
		    	
		    	if(!$parse_image_url['host'])
		    	{
		    		if(!$parse_url['host'])
		    		{
		    			if($parse_url['path'])
		    			{
		    				$imageurl = 'http://'. $parse_url['path'] .'/'.trim($image[0]);
		    			}
		    		}
		    		else
		    		{
		    			$imageurl = 'http://';
		    			$imageurl .= $parse_url['host'];

		    			if (strpos($image[0], '/') === 0 )
		    			{
		    			 	$imageurl .= trim($image[0]);
		    			}
		    			else
		    			{
		    				$imageurl .= '/'. trim($image[0]);
		    			}
		    		}
		    		
		    	}
		    	else if (strpos($image[0], '/') === 0 )
		    	{
				     $imageurl = "http:" . trim($image[0]);
				}
				else
				{
					$imageurl = "http://" . trim($image[0]);
				}

		    }
		   
		    preg_match($imageNames_regex, $image[0], $matches);


		    $imagesize = getimagesize($imageurl);

			if($imagesize){
			    //create a folder with the name 'images'
				if(file_put_contents('images/'.$matches[0], file_get_contents($imageurl)) !== false)
				{
					echo $matches[0] . ': ' . filesize('images/'.$matches[0]) . ' bytes <br/>';
					$imageData[] = $imageurl;
				}
			} 
		}


		$imageData = array_unique($imageData);

	    return $imageData;
	}
	else
	{
		return false;
	}
  
}


$data = image_scrapper( "http://jackandjilltravel.com/highlights-of-sumba-island/" );

echo '<pre>';
print_r($data);
echo "</pre>";


 ?>