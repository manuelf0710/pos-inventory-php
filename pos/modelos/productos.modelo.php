<?php

require_once "conexion.php";

class ModeloProductos{

	/*=============================================
	MOSTRAR PRODUCTOS
	=============================================*/
	static public function mdlMostrarProductos($tabla, $item, $valor, $orden){
		if(isset($_POST['draw'])){
			$draw = $_POST['draw'];
			$row = $_POST['start'];
			$rowperpage = $_POST['length']; // Rows display per page
			$columnIndex = $_POST['order'][0]['column']; // Column index
			$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
			$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
			$searchValue = $_POST['search']['value']; // Search value			

			$searchArray = array();
			
			$searchQuery = " ";
			if($searchValue != ''){
			   $searchQuery = " AND descripcion LIKE :descripcion  ";
			   $searchArray = array( 
					'descripcion'=>"%$searchValue%"
			   );
			}
		}else{
			$draw = '';
			$row = '';
			$rowperpage = '';
			$columnIndex = '';
			$columnName = '';
			$columnSortOrder = '';
			$searchValue = '';

			$searchArray = array();
			
			$searchQuery = " ";			
		}		
		
		if($item != null){
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{
			## Total number of records without filtering
			//$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY $orden DESC");
			$stmt = Conexion::conectar()->prepare("SELECT count(*) AS allcount FROM $tabla");
			$stmt -> execute();
			$records = $stmt->fetch();
			$totalRecords = $records['allcount'];
			
			## Total number of records with filtering
			$stmt = Conexion::conectar()->prepare("SELECT COUNT(*) AS allcount FROM $tabla WHERE 1 ".$searchQuery);
			$stmt->execute($searchArray);
			$records = $stmt->fetch();
			$totalRecordwithFilter = $records['allcount'];	

			## Fetch records
			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE 1 ".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT :limit,:offset");

			// Bind values
			foreach($searchArray as $key=>$search){
				echo("key".$key);
			   $stmt->bindValue(':'.$key, $search,PDO::PARAM_STR);
			}

			$stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
			$stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
			$stmt->execute();
			$empRecords = $stmt->fetchAll();
			$data = array();
			echo($searchQuery);
			foreach($empRecords as $row){
			   $data[] = array(
				  "id"            => $row['id'],
				  "codigo"        => $row['codigo'],
				  "id_categoria"  => $row['id_categoria'],
				  "categoria"     => '',
				  "codigo"        => $row['codigo'],
				  "descripcion"   => $row['descripcion'],
				  "imagen"        => $row['imagen'],
				  "stock"         => $row['stock'],
				  "precio_compra" => $row['precio_compra'],
				  "precio_venta"  => $row['precio_venta'],
				  "fecha"         => $row['fecha'],
				  "especial"	  => 0
			   );
			}
			
			## Response
			$response = array(
			   "draw" => intval($draw),
			   "iTotalRecords" => $totalRecords,
			   "iTotalDisplayRecords" => $totalRecordwithFilter,
			   "aaData" => $data
			);			
			
			//var_dump($empRecords);

			return $response;

		}

		$stmt -> close();

		$stmt = null;

	}
	
	static public function mdlMostrarProductosTable($tabla, $item, $valor, $orden){

		if($item != null){

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id DESC");

			$stmt -> bindParam(":".$item, $valor, PDO::PARAM_STR);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY $orden DESC");

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}	

	/*=============================================
	REGISTRO DE PRODUCTO
	=============================================*/
	static public function mdlIngresarProducto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("INSERT INTO $tabla(id_categoria, codigo, descripcion, imagen, stock, precio_compra, precio_venta) VALUES (:id_categoria, :codigo, :descripcion, :imagen, :stock, :precio_compra, :precio_venta)");

		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	EDITAR PRODUCTO
	=============================================*/
	static public function mdlEditarProducto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET id_categoria = :id_categoria, descripcion = :descripcion, imagen = :imagen, stock = :stock, precio_compra = :precio_compra, precio_venta = :precio_venta WHERE codigo = :codigo");

		$stmt->bindParam(":id_categoria", $datos["id_categoria"], PDO::PARAM_INT);
		$stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_STR);
		$stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
		$stmt->bindParam(":imagen", $datos["imagen"], PDO::PARAM_STR);
		$stmt->bindParam(":stock", $datos["stock"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_compra", $datos["precio_compra"], PDO::PARAM_STR);
		$stmt->bindParam(":precio_venta", $datos["precio_venta"], PDO::PARAM_STR);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}

	/*=============================================
	BORRAR PRODUCTO
	=============================================*/

	static public function mdlEliminarProducto($tabla, $datos){

		$stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");

		$stmt -> bindParam(":id", $datos, PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	ACTUALIZAR PRODUCTO
	=============================================*/

	static public function mdlActualizarProducto($tabla, $item1, $valor1, $valor){

		$stmt = Conexion::conectar()->prepare("UPDATE $tabla SET $item1 = :$item1 WHERE id = :id");

		$stmt -> bindParam(":".$item1, $valor1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $valor, PDO::PARAM_STR);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	MOSTRAR SUMA VENTAS
	=============================================*/	

	static public function mdlMostrarSumaVentas($tabla){

		$stmt = Conexion::conectar()->prepare("SELECT SUM(ventas) as total FROM $tabla");

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;
	}


}