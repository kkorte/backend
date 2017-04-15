        <ul class="nav nav-sidebar"><!-- available classes "right-aligned" -->

            <li>
                <a href="{!! URL::route('hideyo.recipe.index', $recipe->id) !!}">
                    Overview
                </a>
            </li>
            @if(isset($recipeEdit))
            <li class="active">
            @else
            <li>
            @endif
                <a href="{{ URL::route('hideyo.recipe.edit', $recipe->id) }}">
                    <span class="visible-xs"><i class="entypo-gauge"></i></span>
                    <span class="hidden-xs">Edit</span>
                </a>
            </li>

            @if(isset($recipeEditSeo))
            <li class="active">
            @else
            <li>
            @endif
                <a href="{{ URL::route('hideyo.recipe.edit_seo', $recipe->id) }}">
                    <span class="visible-xs"><i class="entypo-gauge"></i></span>
                    <span class="hidden-xs">Seo</span>
                </a>
            </li>  
           
                 
            @if(isset($recipeImages))
            <li class="active">
            @else
            <li>
            @endif
                <a href="{!! URL::route('hideyo.recipe.{recipeId}.images.index', $recipe->id) !!}">
                    <span class="visible-xs"><i class="entypo-user"></i></span>
                    <span class="hidden-xs">Images</span>
                </a>
            </li>

   

        </ul>