<ul class="nav nav-sidebar"><!-- available classes "right-aligned" -->

    <li>
        <a href="{!! URL::route('hideyo.content-group.index', $contentGroup->id) !!}">
            Overview
        </a>
    </li>
    @if(isset($contentGroupEdit))
    <li class="active">
    @else
    <li>
    @endif
        <a href="{{ URL::route('hideyo.content-group.edit', $contentGroup->id) }}">
            <span class="visible-xs"><i class="entypo-gauge"></i></span>
            <span class="hidden-xs">Edit</span>
        </a>
    </li>

    @if(isset($contentGroupEditSeo))
    <li class="active">
    @else
    <li>
    @endif
        <a href="{{ URL::route('hideyo.content-group.edit_seo', $contentGroup->id) }}">
            <span class="visible-xs"><i class="entypo-gauge"></i></span>
            <span class="hidden-xs">Seo</span>
        </a>
    </li>  
   


</ul>