<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">Beheer</a>

		<ul class="nav navbar-nav">
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{!! $this_user->shop->title !!} <span class="caret"></span></a>
				<ul class="dropdown-menu">

					@foreach($available_shops as $shop)

					<li><a href="{{ URL::route('change.language.profile', array('shopId' => $shop->id)) }}">{!! $shop->title !!}</a></li>
					@endforeach
				</ul>
			</li>

		</ul>
			
		</div>

	


		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<li><a href="{{ URL::route('hideyo.shop.index') }}">Shops</a></li>
				<li><a href="{{ URL::route('hideyo.client.index') }}">Clients</a></li>     

				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Orders <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="{{ URL::route('hideyo.order.index') }}">Overview</a></li>
		
						<li><a href="{{ URL::route('hideyo.order-status.index') }}">Statuses</a></li>
						<li><a href="{{ URL::route('hideyo.order-status-email-template.index') }}">Status email templates</a></li>
					</ul>
				</li>                        
		

				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Coupons <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="{{ URL::route('hideyo.coupon.index') }}">Coupons</a></li>
						<li><a href="{{ URL::route('hideyo.coupon-group.index') }}">Groups</a></li>
					</ul>
				</li>


				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Catalog <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="{{ URL::route('hideyo.product.index') }}">Products</a></li>
						<li><a href="{{ URL::route('hideyo.product-category.index') }}">Categories</a></li>
						<li><a href="{{ URL::route('hideyo.brand.index') }}">Brands</a></li>
						<li><a href="{{ URL::route('hideyo.product-tag-group.index') }}">Product tag groups</a></li>
						<li><a href="{{ URL::route('hideyo.extra-field.index') }}">Extra fields</a></li>
						<li><a href="{{ URL::route('hideyo.attribute-group.index') }}">Attribute groups</a></li>
					</ul>
				</li>


				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Content <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="{{ URL::route('hideyo.news.index') }}">News</a></li>
						<li><a href="{{ URL::route('hideyo.news-group.index') }}">News group</a></li>
						<li><a href="{{ URL::route('hideyo.content.index') }}">Static content</a></li>
						<li><a href="{{ URL::route('hideyo.content-group.index') }}">Static content groups</a></li>
						<li><a href="{{ URL::route('hideyo.html-block.index') }}">HTML blocks</a></li>
						<li><a href="{{ URL::route('hideyo.faq.index') }}">Faq items</a></li>
					</ul>
				</li>

				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Admin settings <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="{{ URL::route('hideyo.tax-rate.index') }}">Tax rates</a></li>
						<li><a href="{{ URL::route('hideyo.sending-method.index') }}">Sending methods</a></li>
						<li><a href="{{ URL::route('hideyo.payment-method.index') }}">Payment methods</a></li>
						<li><a href="{{ URL::route('hideyo.user.index') }}">Users</a></li>
						<li><a href="{{ URL::route('hideyo.sending-payment-method-related.index') }}">Order templates</a></li>
						<li><a href="{{ URL::route('hideyo.general-setting.index') }}">General settings</a></li>
						<li><a href="{{ URL::route('hideyo.redirect.index') }}">Redirects</a></li>
					</ul>
				</li>

				<li><a href="{{ URL::route('hideyo.security.logout') }}">Log-out</a></li>
			</ul>

		</div>
	</div>
</nav>

