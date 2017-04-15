<html>
	<body>
		<p>Welkom {{ $firstName }}, <br/><br/>

		Je bent geregistreerd. 
		</p>

		<p>email: {{ $email }}</p>

	    <p>Activeer jouw account hier: </p>

		<p>{!! Html::link('http://'.$shopUrl.'/account/confirm/'.$confirmation_code.'/'.$email.'/'.$register_type, 'activatie link', array('id' => 'linkid')) !!}</p>

	</body>
</html>