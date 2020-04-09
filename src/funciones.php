<?php 

  //configuracion de la conexion a la base de datos

   include('configuracion.php');
   
   session_start();
   
   $peticion = $_POST['peticion'];
   $parametros = $_POST['parametros'];
   
   switch($peticion)
   {
		//Caso para recuperar los edificios de la base de datos
		case 'recupera-edificios-geojson':
		{
			$sql="SELECT row_to_json(fc)
			 FROM ( SELECT 'FeatureCollection' As type, array_to_json(array_agg(f)) As features
			 FROM (SELECT 'Feature' As type
				, ST_AsGeoJSON(lg.the_geom)::json As geometry
				, row_to_json((SELECT l FROM (SELECT osm_id , name, st_area(st_transform(the_geom,3115))/10000 as area_edif ) As l
				  )) As properties
			   FROM edificios_univalle As lg  where ST_IsValid(the_geom) ) As f )  As fc;";
   
			$query = pg_query($dbcon,$sql);
			$row = pg_fetch_row($query);
			echo $row[0];
			break;
		}
		//Caso para recuperar los sitios de interes ( TAREA )
		case 'recupera-sitios-interes-geojson':
		{
			$sql="SELECT row_to_json(fc)
			 FROM ( SELECT 'FeatureCollection' As type, array_to_json(array_agg(f)) As features
			 FROM (SELECT 'Feature' As type
				, ST_AsGeoJSON(lg.the_geom)::json As geometry
				, row_to_json((SELECT l FROM (SELECT osm_id , name, type ) As l
				  )) As properties
			   FROM sitiosinteres_univalle As lg  where ST_IsValid(the_geom) ) As f )  As fc;";
   
			$query = pg_query($dbcon,$sql);
			$row = pg_fetch_row($query);
			echo $row[0];
			break;
		}


		//CASO PARA GENERAR UNA VISTA CON LA RUTA MAS CORTA
		// Tarea remplazar caso, por funcion en plgsql (implementada en clases anteriores)
		case 'genera-ruta-mascorta':
		{
				$x1 = $parametros['x1'];
				$y1 = $parametros['y1'];
				$x2 = $parametros['x2'];
				$y2 = $parametros['y2'];

				//$sql="CREATE OR REPLACE VIEW rutatemporal AS SELECT seq, id1 AS node, id2 AS edge, cost, b.the_geom FROM pgr_dijkstra('
				//, false, false) a LEFT JOIN redpeatonal_univalle b ON (a.id2 = b.gid);";
				$sql= "select _12rutamascortall($x1,$y1,$x2,$y2, 'vista');";

			//Ejecutar QUERY SQL
			$query = pg_query($dbcon,$sql);
					
			if($query)
			{
				//si se ejecuto la consulta con exito retorno un identificador
				echo "NUEVA_RUTA_CREADA";
			}else
			{
				//si NO se ejecuto la consulta retorno un identificador
				echo "NOSEPUDOCREARLARUTA";
			}
			break;
		}

		//CASO PARA RETORNAR LA RUTA GENERADA
		case 'recupera-ruta-geojson':
		{
			$sql=" SELECT row_to_json(fc)
	 FROM ( SELECT 'FeatureCollection' As type, array_to_json(array_agg(f)) As features
	 FROM (SELECT 'Feature' As type
		, ST_AsGeoJSON(lg.the_geom)::json As geometry
		, row_to_json((SELECT l FROM (SELECT node, edge) As l
		  )) As properties
	   FROM vista As lg   ) As f )  As fc;";
			
				$query3 = pg_query($dbcon,$sql);
				$row = pg_fetch_row($query3);
				echo $row[0];
			break;
		}
		
   }
    

?>
