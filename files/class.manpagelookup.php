<?php

//
// class.manpagelookup.php
// version 1.3.0, 2nd January, 2003
//define('__VERSION__', '1.3.2'); // 20080620
define('__VERSION__', '1.3.3'); // 20180116 - php7 compatibility
//
// Description
//
// This is a class allows you to do a man page lookup and view
// the results in a parsed format, including bold, italics and
// coloured text.  A search form is included to allow you to list
// man pages (all of them, all by letter, number, etc.) and to
// offer an easy way to search on different man sections.
//
// Author
//
// Andrew Collington, 2003
// php@amnuts.com, http://php.amnuts.com/
//
// Contributions
//
// Caching idea and code contribution by James Richardson
// Unicode soft-hyphen fix (as used by RedHat) by Dan Edwards
// Some optimisations by Eli Argon
// Based on a C man page viewer by Vadim Pavlov
//
// Feedback
//
// There is message board at the following address:
//
//    http://php.amnuts.com/forums/index.php
//
// Please use that to post up any comments, questions, bug reports, etc.  You
// can also use the board to show off your use of the script.
//
// Support
//
// If you like this script, or any of my others, then please take a moment
// to consider giving a donation.  This will encourage me to make updates and
// create new scripts which I would make available to you.  If you would like
// to donate anything, then there is a link from my website to PayPal.
//
// Example of use
//
//	 require('class.manpagelookup.php');
//	 $mp = new manPageLookUp();
//	 $mp->displayManPage();
//
// Example of using cache and timer
//
//	 require('class.manpagelookup.php');
//	 $mp = new manPageLookUp();
//	 $mp->useTimer(true);
//	 $mp->useCaching(true);
//	 $mp->setCacheDir(dirname(__FILE__) . '/cache/');
//	 $mp->displayManPage();
//	 $mp->displayVersion();
//

error_reporting(E_ERROR);

class manpageLookup
{
	var $command;      // the raw command passed
	var $section;      // the man page section
	var $raw_data;     // the raw data
	var $output;       // the html formatted data
	var $display;      // what groups of man pages to display
	var $width;        // how many columns to display
	var $doemails;     // convert emails to mailto: addresses
	var $showsearch;   // show search box above output
	var $cachedir;     // directory where outputs are cached
	var $use_rawdata;  // do we get the raw data or not?
	var $use_caching;  // do we use caching or not?
	var $use_timer;    // do we use the timer or not?
	var $starttime;    // start time
	var $endtime;      // end time

	/**
	* @return manpageLookup
	* @desc Constructor
	*/
	function manpageLookup()
	{
		$this->command     = $_REQUEST['command'];
		$this->section     = $_REQUEST['section'];
		$this->display     = $_REQUEST['display'];
		$this->output      = '';
		$this->rawdata     = '';
		$this->width       = 4;
		$this->doemails    = true;
		$this->showsearch  = true;
		$this->starttime   = 0;
		$this->endtime     = 0;
		$this->use_timer   = true;
		$this->use_rawdata = false;
		$this->use_caching = false;
		$this->setCacheDir();
	}

	/**
	* @return void
	* @param bool $full
	* @desc Display the class version information
	*/
	function displayVersion($full = false)
	{
		echo "\n\n", '<hr noshade size="1" color="#000000">', "\n";
		echo '<p class="version">Version: '.__VERSION__.' Created with the man page lookup class by Andrew Collington, <a href="mailto:php&#064;amnuts.com">php&#064;amnuts.com</a>';
		if ($full)
		{
			echo '<br />Based on a C man page viewer by Vadim Pavlov<br />';
			echo 'Unicode soft-hyphen fix (as used by RedHat) by Dan Edwards<br />';
			echo 'Some optimisations by Eli Argon<br />';
			echo 'Caching idea and code contribution by <a href="mailto:quien-sabe&#064;metaorg.com">Quien Sabe (aka Jim)</a>';
		}
		echo "</p>\n";
	}

	/**
	* @return void
	* @desc String all html formatting from output and assign to a variable
	*/
	function getRawData()
	{
		$this->raw_data = '';
		$this->raw_data = @strip_tags($this->output);
		$trans = @get_html_translation_table(HTML_ENTITIES);
		$trans = @array_flip($trans);
		$this->raw_data = @strtr($this->raw_data, $trans);
	}

	/**
	* @return void
	* @desc Display the search form
	*/
	function searchManPage()
	{
		$sections = array(
			1 => 'Executable programs or shell commands',
			2 => 'System calls (functions provided by the kernel)',
			3 => 'Library calls (functions within system libraries)',
			4 => 'Special files (usually found in /dev)',
			5 => 'File formats and conventions eg /etc/passwd',
			6 => 'Games',
			7 => 'Macro packages and conventions eg man(7), groff(7)',
			8 => 'System administration commands (usually only for root)',
			9 => 'Kernel routines [Non standard]'
		);
		$alphas = range('A', 'Z');

		// manual search table
		echo '<form action="', $_SERVER['PHP_SELF'], '" method="post">';
		echo '<table border="0" cellpadding="3" cellspacing="1" bgcolor="#DDDDDD" class="manpage-table">';
		echo '<tr><td><strong>Man page search options</strong></td></tr>';
		echo '<tr><td><select name="section"><option value="">Any section</option>';
		foreach ($sections as $num => $type)
		{
			echo "<option value=\"$num\">(S$num) $type</option>\n";
		}
		echo '</select> <input type="text" name="command" size="25"> ';
		echo '<input type="submit" value="search"></td></tr>';
		// a-z listing table
		echo '<tr><td><b>List man pages starting with</b></td></tr>';
		echo '<tr><td>';
		foreach ($alphas as $alpha)
		{
			echo '<a href="', $_SERVER['PHP_SELF'], "?command=&display=$alpha\">$alpha</a> &nbsp; ";
		}
		echo '<a href="', $_SERVER['PHP_SELF'], '?command=&display=ALP">ALPHA</a> &nbsp; ';
		echo '<a href="', $_SERVER['PHP_SELF'], '?command=&display=NUM">NUM</a> &nbsp; ';
		echo '<a href="', $_SERVER['PHP_SELF'], '?command=&display=OTH">OTHER</a> &nbsp; ';
		echo '<a href="', $_SERVER['PHP_SELF'], '?command=&display=ALL">ALL</a>';
		echo "</td></tr>\n</table>\n</form>\n\n";
	}

	/**
	* @return void
	* @param string $name
	* @desc Create the big list of man pages
	*/
	function getBigList($name = '')
	{
		$pipe = popen('man -k a', 'r');
		if (!$pipe)
		{
			echo '<p>Cannot open a pipe to a list of all man pages.</p>';
			return;
		}
		$build = array();
		while (!feof($pipe))
		{
			$s = fgets($pipe,1024);
			$s = trim($s);
			preg_match('/(.*?) \((.*?)\)(\s)+- (.*)/i', $s, $matches);
			if (preg_match('/\[([^\]]*)\]/', $matches[1], $submatches))
			{
				$match = trim($submatches[1]);
			}
			else
			{
				$match = trim($matches[1]);
			}
			switch($this->display)
			{
				case 'ALL':
					if ($matches[1])
					{
						$build[$match] = trim($matches[4]);
					}
					break;
				case 'NUM':
					if (preg_match('/^[0-9]/', $match))
					{
						$build[$match] = trim($matches[4]);
					}
					break;
				case 'ALP':
					if (preg_match('/^[a-zA-Z]/', $match))
					{
						$build[$match] = trim($matches[4]);
					}
					break;
				case 'OTH':
					if (preg_match('/^[^a-zA-Z0-9]/', $match))
					{
						$build[$match] = trim($matches[4]);
					}
					break;
				default:
					if (preg_match("/^{$this->display}/i", $match))
					{
						$build[$match] = trim($matches[4]);
					}
					break;
			}
		}
		pclose($pipe);
		ksort($build);
		// create output
		$cnt = 0;
		$this->output = '<table border="0" border="0" width="100%" cellpadding="5"><tr>';
		foreach ($build as $page => $title)
		{
			if ($cnt++ == $this->width)
			{
				$this->output .= "</tr>\n<tr>\n";
				$cnt = 1;
			}
			$this->output .= sprintf('<td><a href="%s?command=%s" title="%s">%s</a></td>', $_SERVER['PHP_SELF'], urlencode($page), htmlentities($title), htmlentities($page));
		}
		$this->output .= "</tr>\n</table>\n";
		$this->output .= sprintf("<p>%d man page%s</p>\n", count($build), (count($build) == 1 ? '' : 's'));

		// create plain text version
		if ($this->use_rawdata)
		{
			$this->getRawData();
		}
		// cache
		if ($this->use_caching)
		{
			$this->saveCache($name);
		}
	}

	/**
	* @return void
	* @desc Compile the man page and format as needs be
	*/
	function buildManPage()
	{
		$cmd = ($this->command == '') ? 'man' : $this->command;
		$exe = 'man ' . ($this->section ? "-S{$this->section} " : '') . EscapeShellCmd($cmd);
		$pipe = popen($exe, 'r');
		if (!$pipe)
		{
			echo '<p>Cannot open a pipe to the man page.</p>';
			return;
		}

		$build = '';

		while (!feof($pipe))
		{
			$s = fgets($pipe, 1024);
			$len = strlen($s);
      $peekmax = $len - 1;
			for ($i = 0; $i < $len; $i++)
			{
				switch (ord($s[$i]))
				{
					case 8:
						break;
					case 0xAD:
						// Unicode soft hyphen
						$build .= '-';
						break;
					default:
						if ($i < $peekmax && ord($s[$i+1]) == 8)
						{
							break;
						}
						if (ord($s[$i-1]) == 8)
						{
							if ($s[$i-2] == $s[$i])
							{
								if ($italic)
								{
									$build .= '</em></font>';
									$italic = 0;
								}
								if ($bold)
								{
									$build .= htmlentities($s[$i]);
								}
								else
								{
									$build .= '<strong>' . htmlentities($s[$i]);
									$bold = 1;
								}
							}
							else if ($s[$i-2] == '_')
							{
								if ($bold)
								{
									$build .= '</strong>';
									$bold = 0;
								}
								if ($italic)
								{
									$build .= htmlentities($s[$i]);
								}
								else
								{
									$build .= '<font color="#0000FF"><em>' . htmlentities($s[$i]);
									$italic = 1;
								}
							}
						}
						else
						{
							if ($italic)
							{
								$build .= '</em></font>';
								$italic = 0;
							}
							if ($bold)
							{
								$build .= '</strong>';
								$bold = 0;
							}
							$build .= htmlentities($s[$i]);
						}
						break;
				}
			}
		}
		pclose($pipe);

		// create formatted version
		$this->output = '';
		$this->output = preg_replace('/\n\n\n+/', "\n\n", $build);
		if ($this->doemails)
		{
			$this->output = preg_replace('/[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,3}/i', '<a href="mailto:\\0">\\0</a>', $this->output);
		}

		// create plain text version
		if ($this->use_rawdata)
		{
			$this->getRawData();
		}

		// cache
		if ($this->use_caching)
		{
			$this->saveCache();
		}
	}

	/**
	* @return void
	* @desc Display the man page
	*/
	function displayManPage()
	{
		// security check here before the command is processed at all
		// make sure not to include any /, <, ;, etc
		if ($this->command != '')
		{
			if (preg_match('![<>/;]!', $this->command))
			{
				if ($this->showsearch)
				{
					$this->searchManPage();
				}
				echo "<p>Cannot display that man page.</p>";
				return;
			}
		}

		if ($this->use_timer)
		{
			$this->startTimer();
		}

		// setup the man page/search form
		if ($this->command == '' && $this->display == '')
		{
			$this->searchManPage();
			return;
		}
		else
		{
			if ($this->display != '')
			{
				switch($this->display)
				{
					case 'ALL':
						$tmpCacheName = 'cacheALL';
						break;
					case 'NUM':
						$tmpCacheName = 'cacheNUM';
						break;
					case 'ALP':
						$tmpCacheName = 'cacheALP';
						break;
					case 'OTH':
						$tmpCacheName = 'cacheOTH';
						break;
					default:
						$tmpCacheName = "cache{$this->display}";
						break;
				}
				if ($this->use_caching && $this->checkCache($tmpCacheName)) ;
				else $this->getBigList($tmpCacheName);
			}
			else if ($this->use_caching && $this->checkCache()) ;
			else $this->buildManPage();
		}

		// show the man page/list results
		if ($this->showsearch)
		{
			$this->searchManPage();
		}
		if ($this->command)
		{
			if ($this->output == '')
			{
				echo "<p>Could not display man page for <strong>{$this->command}</strong>";
				if ($this->section)
				{
					echo " (using -S{$this->section})";
				}
				echo "</p>\n";
			}
			else
			{
				echo "<pre>{$this->output}</pre>\n";
			}
		}
		else
		{
			if ($this->output)
			{
				echo $this->output;
			}
			else
			{
				switch($this->display)
				{
					case 'ALL':
						echo '<p>No man pages were found or could be listed.</p>';
						break;
					case 'NUM':
						echo '<p>No man pages starting with a digit were found or could be listed.</p>';
						break;
					case 'ALP':
						echo '<p>No man pages starting with an alpha character were found or could be listed.</p>';
						break;
					case 'OTH':
						echo '<p>No man pages starting with a character other than alphanumeric were found or could be listed.</p>';
						break;
					default:
						echo "<p>No man pages starting with {$this->display} were found or could be listed.</p>";
						break;
				}
			}
		}
		if ($this->use_timer)
		{
			$this->endTimer();
			$this->showTimer();
		}
	}


	//
	// Caching functions
	//


	/**
	* @return void
	* @param bool $do
	* @desc Cache the generated man pages or not
	*/
	function useCaching($do = false)
	{
		$this->use_caching = ($do == true ? true : false);
	}

	/**
	* @return void
	* @param string $dir
	* @desc Set the cache directory
	*/
	function setCacheDir($dir = '/var/tmp/man-cache-html/')
	{
		$this->cachedir = $dir . ($dir[strlen($dir)-1] != '/' ? '/' : '');
	}

	/**
	* @return string
	* @param string $name
	* @desc Get the filename of a cache file based on the man page name
	*/
	function getCacheName($name = '')
	{
    if($name) {
      $hashphrase = $name;
    }
    else {
      $hashphrase = $this->command;
      if($this->section) {
        $hashphrase .= $this->section;
      }
    }
		return ($this->cachedir . md5(trim($hashphrase)) . ".html");
	}

	/**
	* @return bool
	* @param string $name
	* @desc Check to see a cache file exists and load if it does
	*/
	function checkCache($name = '')
	{
		$fn = $this->getCacheName($name);
		$fs = @filesize($fn);
		if ($fs)
		{
			$fp = @fopen($fn, 'r');
			if ($fp !== false)
			{
				$this->output = fread($fp, $fs);
				fclose($fp);
				return true;
			}
		}
		return false;
	}

	/**
	* @return bool
	* @param string $name
	* @desc Save the generated man page
	*/
	function saveCache($name = '')
	{
		$fp = fopen($this->getCacheName($name), 'w');
		if ($fp)
		{
			fwrite($fp, $this->output);
			fclose($fp);
			return true;
		}
		return false;
	}


	//
	// Timer functions
	//


	/**
	* @return void
	* @param bool $do
	* @desc Use the timer or not
	*/
	function useTimer($do = true)
	{
		$this->use_timer = ($do == true ? true : false);
	}

	/**
	* @return void
	* @desc Set the time for the start of the timer
	*/
	function startTimer()
	{
		list($usec, $sec) = explode(' ', microtime());
		$this->starttime = ((float)$usec + (float)$sec);
	}

	/**
	* @return void
	* @desc Set the time for the end of the timer
	*/
	function endTimer()
	{
		list($usec, $sec) = explode(' ', microtime());
		$this->endtime = ((float)$usec + (float)$sec);
	}

	/**
	* @return void
	* @desc Show the timer details
	*/
	function showTimer()
	{
		echo sprintf("<p>Time taken: %0.05f seconds</p>\n", ($this->endtime - $this->starttime));
	}

}

?>
