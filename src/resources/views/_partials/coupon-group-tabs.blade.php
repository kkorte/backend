<ul class="nav nav-sidebar"><!-- available classes "right-aligned" -->

    <li>
        <a href="{!! URL::route('hideyo.coupon-group.index', $couponGroup->id) !!}">
            Overview
        </a>
    </li>
    @if(isset($couponGroupEdit))
    <li class="active">
    @else
    <li>
    @endif
        <a href="{{ URL::route('hideyo.coupon-group.edit', $couponGroup->id) }}">
            <span class="visible-xs"><i class="entypo-gauge"></i></span>
            <span class="hidden-xs">Edit</span>
        </a>
    </li>
</ul>