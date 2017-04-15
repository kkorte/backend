<html>
	<body>
		<p>Welkom, <br/><br/>
		Je kunt via deze link jouw wachtwoord wijzigen. </p>
		<p>{!! Html::link('http://'.$shopUrl.'/account/reset-password/'.$code.'/'.$email, 'wachtwoord resetten', array('id' => 'linkid')) !!}</p>
	</body>
</html>