<ul class="nav nav-sidebar"><!-- available classes "right-aligned" -->

    <li>
        <a href="{!! URL::route('hideyo.content.index', $content->id) !!}">
            Overview
        </a>
    </li>
    @if(isset($contentEdit))
    <li class="active">
    @else
    <li>
    @endif
        <a href="{{ URL::route('hideyo.content.edit', $content->id) }}">
            <span class="visible-xs"><i class="entypo-gauge"></i></span>
            <span class="hidden-xs">Edit</span>
        </a>
    </li>

    @if(isset($contentEditSeo))
    <li class="active">
    @else
    <li>
    @endif
        <a href="{{ URL::route('hideyo.content.edit_seo', $content->id) }}">
            <span class="visible-xs"><i class="entypo-gauge"></i></span>
            <span class="hidden-xs">Seo</span>
        </a>
    </li>  
   
 
    @if(isset($contentImages))
    <li class="active">
    @else
    <li>
    @endif
        <a href="{!! URL::route('hideyo.content-image.index', $content->id) !!}">
            <span class="visible-xs"><i class="entypo-user"></i></span>
            <span class="hidden-xs">Images</span>
        </a>
    </li>




</ul>