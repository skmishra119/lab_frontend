<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

global $lab_id;

// Category get by id API 
$app->get('/api/categories/{lids}', function($request){
	$lu_ids = explode('::',$request->getAttribute('lids'));
	$lab_id = trim($lu_ids[0]);
	//$uid = trim($lu_ids[1]);

	$qry="select c.id, c.name, c.description, d.name as parent, c.status, date_format(c.updated,'%b %d, %Y %H:%i:%s') as updated from bl_categories c left join bl_categories d on c.parent_id=d.id and d.status='ACTIVE' where c.status='ACTIVE'";
	try{
		$lab_db = new lab_db();
		$lab_db = $lab_db->connect($lab_id);
		if($lab_db==null) {
			throw new PDOException("Internal server error in connecting databases", 1);
		}
		$stmt = $lab_db->query($qry);
		$data = $stmt->fetchAll(PDO::FETCH_OBJ);
		$lab_db = null;
		echo json_encode($data);
	} catch(PDOException $e){
		echo '{"message" : {type": "Error", "msg": "'.$e->getMessage().'"}}';
	}
});


$app->get('/api/category/{lids}', function($request){
	$lu_ids = explode('::',$request->getAttribute('lids'));
	$lab_id = trim($lu_ids[0]);
	$cid = trim($lu_ids[1]);

	$qry="select id, name, description, parent_id, status from bl_categories where id='$cid' AND status='ACTIVE'";
	try{
		$lab_db = new lab_db();
		$lab_db = $lab_db->connect($lab_id);
		if($lab_db==null) {
			throw new PDOException("Internal server error in connecting databases", 1);
		}
		$stmt = $lab_db->query($qry);
		$data['data'] = $stmt->fetchAll(PDO::FETCH_OBJ);
		$lab_db = null;
		if(sizeof($data['data'])>0){
			$data['message'] = array('type'=>'success', 'msg'=>'Success');
		} else {
			$data['data'] = array(array('token'=>null));
			$data['message'] = array('type'=>'Error', 'msg'=>'No data available!');	
		}
		echo json_encode(array_reverse($data));
	} catch(PDOException $e){
		echo '{"message" : {type": "Error", "msg": "'.$e->getMessage().'"}}';
	}
});

// Category get by parent id API 
$app->get('/api/categories/parent/{lids}', function($request){	
	$lu_ids = explode('::',$request->getAttribute('lids'));
	$lab_id = trim($lu_ids[0]);
	$pid = trim($lu_ids[1]);
	
	$qry="SELECT id as cat_id, name as parent FROM bl_categories WHERE parent_id=:parent_id";
	try{
		$lab_db = new lab_db();
		$lab_db = $lab_db->connect($lab_id);
		if($lab_db==null) {
			throw new PDOException("Internal server error in connecting databases", 1);
		}
		$stmt = $lab_db->prepare($qry);
		$stmt->bindParam(':parent_id', $pid, PDO::PARAM_STR);
		$stmt->execute();
		$data['data'] = $stmt->fetchAll(PDO::FETCH_OBJ);
		if(sizeof($data['data'])>0){
			$data['message'] = array('type'=>'success', 'msg'=>'Success');
		} else {
			$data['data'] = array(array('token'=>null));
			$data['message'] = array('type'=>'Error', 'msg'=>'No data available!');	
		}
		echo json_encode(array_reverse($data));	
	} catch(PDOException $e){
		echo '{"message" : {type": "Error", "msg": "'.$e->getMessage().'"}}';
	}
});

// Category add API 
$app->post('/api/category/{lids}', function($request){
	
	$lu_ids = explode('::',$request->getAttribute('lids'));
	$lab_id = trim($lu_ids[0]);
	
	$IdConf = new uuid_config();
	$newId = $IdConf->generate();
	
	$name 			= $request->getParam('name');
	$description 	= $request->getParam('description');
	$parent_id 		= $request->getParam('parent_id');
	$status 		= $request->getParam('status');
	// $qry 			= "insert into bl_categories (name, description, parent_id, status) values ( :name, :description, :parent_id, :status)";
	$qry 			= "INSERT INTO bl_categories 
								SET id = :newId, 
								name = :name, 
				            	description = :description, 
				            	parent_id = :parent_id,  
				            	status = :status";	
	
	try{
		$lab_db = new lab_db();
		$lab_db = $lab_db->connect($lab_id);
		if($lab_db==null) {
			throw new PDOException("Internal server error in connecting databases", 1);
		}
		$stmt = $lab_db->prepare($qry);
		$stmt->bindParam(':newId', $newId, PDO::PARAM_STR);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':description', $description, PDO::PARAM_STR);
		$stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_STR);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->execute();

		$data['message'] = array('type'=>'success', 'msg'=>'Insert Successfully.');	
		echo json_encode(array_reverse($data));	
	} catch(PDOException $e){
		echo '{"message" : {type": "Error", "msg": "'.$e->getMessage().'"}}';
	}
	
});

// Category update API 
$app->put('/api/category/{lids}', function($request){

	// $lab_id = '4d5b7f24-0b5e-11e9-89cd-0208c7f15232';
	$lu_ids = explode('::',$request->getAttribute('lids'));
	$lab_id = trim($lu_ids[0]);
	$cid = trim($lu_ids[1]);

	$name 			= $request->getParam('name');
	$description 	= $request->getParam('description');
	$parent_id 		= $request->getParam('parent_id');
	$status 		= $request->getParam('status');
	$qry 			= "UPDATE bl_categories 
								SET name = :name, 
				            		description = :description, 
				            		parent_id = :parent_id,  
				            		status = :status 
				            	WHERE id = :cid";
	
	try{
		$lab_db = new lab_db();
		$lab_db = $lab_db->connect($lab_id);
		if($lab_db==null) {
			throw new PDOException("Internal server error in connecting databases", 1);
		}
		$stmt = $lab_db->prepare($qry);
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);
		$stmt->bindParam(':description', $description, PDO::PARAM_STR);
		$stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_STR);
		$stmt->bindParam(':status', $status, PDO::PARAM_STR);
		$stmt->bindParam(':cid', $cid, PDO::PARAM_STR);
		$stmt->execute();

		$data['message'] = array('type'=>'success', 'msg'=>'Update Successfully.');	
		echo json_encode(array_reverse($data));	
	} catch(PDOException $e){
		echo '{"message" : {type": "Error", "msg": "'.$e->getMessage().'"}}';
	}
	
});

// Category Delete API 
$app->delete('/api/category/{lids}', function($request, $response, $args){
	$lu_ids = explode('::',$request->getAttribute('lids'));
	$lab_id = trim($lu_ids[0]);
	$cid = trim($lu_ids[1]);
	// $lab_id = '4d5b7f24-0b5e-11e9-89cd-0208c7f15232';
	$id 	= $request->getAttribute('id');
	$qry 	= "UPDATE bl_categories SET status = 'DELETED' WHERE id = :cid";
	try{
		$lab_db = new lab_db();
		$lab_db = $lab_db->connect($lab_id);
		if($lab_db==null) {
			throw new PDOException("Internal server error in connecting databases", 1);
		}
		$stmt = $lab_db->prepare($qry);		
		$stmt->bindParam(':cid', $cid, PDO::PARAM_STR);
		$stmt->execute();
		$data['message'] = array('type'=>'success', 'msg'=>'Deleted successfully');	
		echo json_encode(array_reverse($data));
	} catch(PDOException $e){
		echo '{"message" : {type": "Error", "msg": "'.$e->getMessage().'"}}';
	}
});	