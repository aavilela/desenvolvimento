<!DOCTYPE html>
<html lang="en">
	<head>
		<title>teste movimento</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
		<style>
			body {
				font-family: Monospace;
				background-color: #f0f0f0;
				margin: 0px;
				overflow: hidden;
			}
		</style>
	</head>
	<body>

		<?php
			$arquivo = fopen('pos.txt','r+');
			if ($arquivo == false) die('O arquivo não existe.');
			$linhas = array();
			$i = 0;
			while(true) {
				$linhas[$i] = fgets($arquivo);
				if ($linhas[$i]==null) break;
				$i = $i + 1;
			}
		?>

		<script src="js/three.min.js"></script>
		<script src="js/TrackballControls.js"></script>
		<script src="js/JSONLoader.js">> </script>


		<script>
			var container;
			var camera, controls, scene, renderer;
			var objects = [];
			var plane;

			var raycaster = new THREE.Raycaster();
			var mouse = new THREE.Vector2(),
			offset = new THREE.Vector3(),
			INTERSECTED, SELECTED;

			init();
			animate();

			function init() {

				container = document.createElement( 'div' );
				document.body.appendChild( container );

				camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 1, 10000 );
				camera.position.z = 1000;
				camera.position.y = 100;
				camera.rotation.x = 180 * Math.PI / 180;

				controls = new THREE.TrackballControls( camera );
				controls.rotateSpeed = 1.0;
				controls.zoomSpeed = 1.2;
				controls.panSpeed = 0.8;
				/*controls.noZoom = false;
				controls.noPan = false;
				controls.staticMoving = true;
				controls.dynamicDampingFactor = 0.3;*/

				scene = new THREE.Scene();

				scene.add( new THREE.AmbientLight( 0x505050 ) );

				var light = new THREE.DirectionalLight( 0xffffff, 1 );
				light.position.set( 0, 500, 0 ).normalize();
				light.rotation.x = 180 * Math.PI / 180;

				/*light.shadowCameraNear = 200;
				light.shadowCameraFar = camera.far;
				light.shadowCameraFov = 50;

				light.shadowBias = -0.00022;
				light.shadowDarkness = 0.5;

				light.shadowMapWidth = 2048;
				light.shadowMapHeight = 2048;*/

				scene.add( light );

				var geometry = new THREE.BoxGeometry( 7059, 3246, 1 );
				var textura = THREE.ImageUtils.loadTexture('img/mapacompleto.jpg');
				var material = new THREE.MeshLambertMaterial( { map: textura } );
				plano = new THREE.Mesh(geometry,material);
				plano.rotation.x = 90 * Math.PI / 180;
				scene.add(plano);

				//var geometry = new THREE.BoxGeometry( 40, 40, 40 );

				/*for ( var i = 0; i < 1; i ++ ) {

					var object = new THREE.Mesh( geometry, new THREE.MeshLambertMaterial( { color: Math.random() * 0xffffff } ) );

					object.position.x = Math.random() * 1000 - 500;
					object.position.y = Math.random() * 600 - 300;
					object.position.z = Math.random() * 800 - 400;

					object.rotation.x = Math.random() * 2 * Math.PI;
					object.rotation.y = Math.random() * 2 * Math.PI;
					object.rotation.z = Math.random() * 2 * Math.PI;

					object.scale.x = Math.random() * 2 + 1;
					object.scale.y = Math.random() * 2 + 1;
					object.scale.z = Math.random() * 2 + 1;

					object.castShadow = true;
					object.receiveShadow = true;

					scene.add( object );

					objects.push( object );

				}*/

				var loader = new THREE.JSONLoader();
				loader.load( "mods/museunatural_ch_yan.js", function( geometry ) {
					var material = new THREE.MeshPhongMaterial( { color:0xacdfe3 } );
					var mesh = new THREE.Mesh( geometry, material );
					var ax = <?php echo $linhas[1]; ?>;
					var ay = <?php echo $linhas[2]; ?>;
					alert(ax);
					mesh.position.set( ax , ay , 900 );
					mesh.scale.set(10, 10, 10);

					scene.add( mesh );
					objects.push( mesh );
				});

				plane = new THREE.Mesh(
					new THREE.PlaneBufferGeometry( 2000, 2000, 8, 8 ),
					new THREE.MeshBasicMaterial( { color: 0x000000, opacity: 0.25, transparent: true } )
				);
				plane.visible = false;
				scene.add( plane );

				renderer = new THREE.WebGLRenderer( { antialias: true } );
				renderer.setClearColor( 0x000000 );
				renderer.setPixelRatio( window.devicePixelRatio );
				renderer.setSize( window.innerWidth, window.innerHeight );
				renderer.sortObjects = false;

				renderer.shadowMapEnabled = true;
				renderer.shadowMapType = THREE.PCFShadowMap;

				container.appendChild( renderer.domElement );

				renderer.domElement.addEventListener( 'mousemove', onDocumentMouseMove, false );
				renderer.domElement.addEventListener( 'mousedown', onDocumentMouseDown, false );
				renderer.domElement.addEventListener( 'mouseup', onDocumentMouseUp, false );

				//

				window.addEventListener( 'resize', onWindowResize, false );

			}

			function onWindowResize() {

				camera.aspect = window.innerWidth / window.innerHeight;
				camera.updateProjectionMatrix();

				renderer.setSize( window.innerWidth, window.innerHeight );

			}

			function onDocumentMouseMove( event ) {

				event.preventDefault();

				mouse.x = ( event.clientX / window.innerWidth ) * 2 - 1;
				mouse.y = - ( event.clientY / window.innerHeight ) * 2 + 1;

				//teste2

				raycaster.setFromCamera( mouse, camera );

				if ( SELECTED ) {

					var intersects = raycaster.intersectObject( plane );
					//SELECTED.position.copy( intersects[ 0 ].point.sub( offset ) );
					SELECTED.position.z = intersects [ 0 ].point.z;
					return;

				}

				var intersects = raycaster.intersectObjects( objects );

				if ( intersects.length > 0 ) {

					if ( INTERSECTED != intersects[ 0 ].object ) {

						if ( INTERSECTED ) INTERSECTED.material.color.setHex( INTERSECTED.currentHex );

						INTERSECTED = intersects[ 0 ].object;
						INTERSECTED.currentHex = INTERSECTED.material.color.getHex();

						plane.position.copy( INTERSECTED.position );
						plane.lookAt( camera.position );

					}

					container.style.cursor = 'pointer';

				} else {

					if ( INTERSECTED ) INTERSECTED.material.color.setHex( INTERSECTED.currentHex );

					INTERSECTED = null;

					container.style.cursor = 'auto';

				}

			}

			function onDocumentMouseDown( event ) {

				event.preventDefault();

				var vector = new THREE.Vector3( mouse.x, mouse.y, 0.5 ).unproject( camera );

				var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );

				var intersects = raycaster.intersectObjects( objects );

				if ( intersects.length > 0 ) {

					controls.enabled = false;

					SELECTED = intersects[ 0 ].object;

					var intersects = raycaster.intersectObject( plane );
					offset.copy( intersects[ 0 ].point ).sub( plane.position );
					container.style.cursor = 'move';

				}

			}

			function onDocumentMouseUp( event ) {

				event.preventDefault();

				controls.enabled = true;

				if ( INTERSECTED ) {

					plane.position.copy( INTERSECTED.position.x );

					SELECTED = null;

				}

				container.style.cursor = 'auto';

			}

			//

			function animate() {

				requestAnimationFrame( animate );

				render();

			}

			function render() {

				controls.update();

				renderer.render( scene, camera );

			}

		</script>

	</body>
</html>
