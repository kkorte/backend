<?php namespace Hideyo\Backend\Services;

use \Html;

class FormBuilder extends \Collective\Html\FormBuilder
{

    public function tree($items, $output = false)
    {

        if ($output) {
            $output = '';
        }
        foreach ($items as $item) {
            if (!empty($item['children'])) {
                $output .= "<li>".$item['title']."<ul>\n";
                $output .= $this->tree($item['children'], $output);
                $output .= '</ul></li>';
            } else {
                  $output .= '<li>'.$item['title'].'</li>';
            }
        }

        return $output;
    }


    public function deleteajax($url, $button_label = 'Delete', $form_parameters = array(), $button_options = array(), $title = false)
    {
        if (empty($form_parameters)) {
            $form_parameters = array(
            'method'=>'DELETE',
            'class' =>'delete-form delete-button',
            'url'   =>$url,
            'style' => 'display:inline',
            'data-title' => $title
            );
        } else {
            $form_parameters['url'] = $url;
            $form_parameters['method'] = 'DELETE';
        };

        return \Form::open($form_parameters)
            . \Form::submit($button_label, $button_options)
            . \Form::close();
    }

    public function submit($value = null, $options = [])
    {
        $options['class'] = 'btn btn-cons btn-success' . (isset($options['class']) ? ' ' . $options['class'] : '');

        return parent::submit($value, $options);
    }

    public function multiselect2($name, array $list = [], array $selected = [], $options = [], $placeholder = 'Select one...')
    {
        $options['name'] = $name;
        $html = array();
        if (is_array($selected)) {
            foreach ($selected as $key => $value) {
                      $selected[$value] = $value;
            }
        }

        //dd($list, $selected);
        foreach ($list as $value => $display) {
            $sel = isset($selected[$value])?' selected="selected"':'';
            $html[] = '<option value="'.$value.'"'.$sel.'>'.e($display).'</option>';
        }
     
        // build out a final select statement, which will contain all the values.
        $options = Html::attributes($options);
        
        if ($html) {
            $list = implode('', $html);
        } else {
            $list = "";
        }
     
        return "<select multiple {$options} class=\"select2 form-control\">{$list}</select>";
    }


    public function select2($name, array $list = [], $selected, $options = [], $placeholder = 'Select one...')
    {
        $options['name'] = $name;
        $html = array();


        //dd($list, $selected);
        foreach ($list as $value => $display) {
            $sel = '';
            if ($selected == $value) {
                $sel = ' selected="selected"';
            }
            
            $html[] = '<option value="'.$value.'"'.$sel.'>'.e($display).'</option>';
        }
     
        // build out a final select statement, which will contain all the values.
        $options = HTML::attributes($options);
        
        if ($html) {
            $list = implode('', $html);
        } else {
            $list = "";
        }
     
        return "<select {$options} class=\"select2 form-control\">{$list}</select>";
    }
}
