<?php
function appendsqlwhere($where, $newclause)
{
	if(strpos(strtolower($where),'where') === FALSE)
	{ 
		return $where.' where '.$newclause; 
	} 
	else 
	{ 
		return $where.' and '.$newclause; 
	}
}


function checkandset($varname, $value)
{
	if (!isset($_GET[$varname]))
	{
		return $value;  // default 
	}
	else
	{
		return $_GET[$varname];
	}
}

function curl_get_contents($url)
{
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}


function html($text)
{
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function htmlout($text)
{
  echo html($text);
}

function htmlcalcchecked($value1, $value2)
{
	if($value1==$value2)
	{
		htmlout(' Checked');
	}
}

function htmloutstatus($text, $qty)
{
	if($qty==0)
	{
		echo '<TD BGCOLOR="#FF0000">' . html($text) . '</TD>';
	}
	elseif($qty<5)
	{
		echo '<TD BGCOLOR="#FFCC33">' . html($text) .' ('.html($qty).')</TD>';
	}
	else
	{
		echo '<TD BGCOLOR="#00FF00">' . html($text) . ' ('.html($qty).')</TD>';
	}

}


function markdown2html($text)
{
  $text = html($text);

  // strong emphasis
  $text = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $text);
  $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);

  // emphasis
  $text = preg_replace('/_([^_]+)_/', '<em>$1</em>', $text);
  $text = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $text);

  // Convert Windows (\r\n) to Unix (\n)
  $text = str_replace("\r\n", "\n", $text);
  // Convert Macintosh (\r) to Unix (\n)
  $text = str_replace("\r", "\n", $text);

  // Paragraphs
  $text = '<p>' . str_replace("\n\n", '</p><p>', $text) . '</p>';
  // Line breaks
  $text = str_replace("\n", '<br>', $text);

  // [linked text](link URL)
  $text = preg_replace(
      '/\[([^\]]+)]\(([-a-z0-9._~:\/?#@!$&\'()*+,;=%]+)\)/i',
      '<a href="$2">$1</a>', $text);

  return $text;
}

function markdownout($text)
{
  echo markdown2html($text);
}
