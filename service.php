<?php

function showResults($cursor, $name, $details) {
		$count = $cursor->count();
		echo '<div class="group">'.$name.' ('.$count.')</div>';
		if($details)
		{
			if($cursor)
				$cursor->sort( array('date'=> -1));
			foreach($cursor as $id => $value) {  
				$v = print_r($value['title'], true);
				echo '<li';
				if($count == 1)
					echo ' class="focus"';
				echo ' onclick="find(\''.preg_quote($v).'\');">';
				echo '<span class="title">';
				if($c = strstr($v, ":", true))
				{
					echo $c;
					$r = substr(strstr($v, ":"), 1);
					echo '</span>';
					echo '<div class="details">'.$r.'</div>';
				}
				else
				{
					echo substr($v, 0, 30);

					if(strlen($v) > 30)
					{
						echo '...';
						echo '</span>';
						echo '<div class="details">'.$v.'</div>';
					}
					else
						echo '</span>';
				}

				echo '<div class="info">';
				echo '<div class="date">Created : ';
				echo date('d/m/y G:i', $value['date']->sec);
				echo '</div>';

				if(isset($value['archive_date']))	
				{
					echo '<div class="date">Unfocused : ';
					echo date('d/m/y G:i', $value['archive_date']->sec);
					echo '</div>';
				}
				echo '</div>';

				echo  '</li>';
			}
		}  	

}

	function connect($collection_name) {
		$dbhost = 'localhost';  
		$dbname = 'task';  
		// Connect to test database  
		$m = new Mongo("mongodb://$dbhost");  
		$db = $m->$dbname;  
		// select the collection  
		return $db->$collection_name;  
	}

	if( isset($_GET['list']) )
	{
		try {
		$collection = connect("tasks");
		$cursor = $collection->find();  
		showResults($cursor, "Focus", true);
		$collection = connect("archives");
		$cursor = $collection->find();  
		showResults($cursor, "UnFocus", false);

		}
		catch (Exception $e) {
		    echo 'Exception : ',  $e->getMessage(), "\n";
		}
	}

	if( isset($_GET['find']))
	{
		try {
		$collection = connect("tasks");
		$cursor = $collection->find( array('title' => new MongoRegex('/' . $_GET['find'] . '/') ));
		if($cursor->count() == 0)
			$cursor = $collection->find(array('title' => $_GET['find']));
		showResults($cursor, "Focus", true);
		echo '<hr/>';
		$collection = connect("archives");
		$cursor = $collection->find( array('title' => new MongoRegex('/' . $_GET['find'] . '/') ));
		showResults($cursor, "UnFocus", true);

		}
		catch (Exception $e) {
		    echo 'Exception : ',  $e->getMessage(), "\n";
		}
	}

	if( isset($_GET['push'] ))
	{
		try {

		$collection = connect("tasks");
		
		$cursor = $collection->insert( array( "title" => $_GET['push'], "date" => new MongoDate() ) );  

		}
		catch (Exception $e) {
		    echo 'Exception : ',  $e->getMessage(), "\n";
		}
	}

	if( isset($_GET['clear'] ))
	{
		try {

		$collection = connect("tasks");
		
		$cursor = $collection->remove( array() );  

		}
		catch (Exception $e) {
		    echo 'Exception : ',  $e->getMessage(), "\n";
		}
	}	

	if( isset($_GET['remove'] ))
	{
		try {

		$collection = connect("tasks");
		$cursor = $collection->remove( array( 'title' => new MongoRegex('/' . $_GET['remove'] . '/')) );  

		}
		catch (Exception $e) {
		    echo 'Exception : ',  $e->getMessage(), "\n";
		}
	}	

	if( isset($_GET['archive'] ))
	{
		try {

		$collection = connect("tasks");
		$cursor = $collection->find( array( 'title' => new MongoRegex('/' . $_GET['archive'] . '/')) );  

		$archive = connect("archives");
		foreach($cursor as $key => $document)
		{
			$archive->insert($document);
			$archive->update($document, array( '$set' => array( 'archive_date' => new MongoDate() ) ) );
			$collection->remove($document);
		}
		}
		catch (Exception $e) {
		    echo 'Exception : ',  $e->getMessage(), "\n";
		}
	}	
	if( isset($_GET['unarchive'] ))
	{
		try {

		$collection = connect("archives");
		$cursor = $collection->find( array( 'title' => new MongoRegex('/' . $_GET['unarchive'] . '/')) );  

		$archive = connect("tasks");
		foreach($cursor as $key => $document)
		{
			$archive->insert($document);
			$collection->remove($document);
		}
		}
		catch (Exception $e) {
		    echo 'Exception : ',  $e->getMessage(), "\n";
		}
	}	

?>
