<?php

namespace App\Utils;

class HtmlTable {
    
    public $right_align_numeric = true;
    
    public $table_attrs;    // assoc array.  keys: class, id, cellspacing, cellpadding, bgcolor, etc.
    public $header_attrs;   // assoc array.  keys:  bgcolor, ...
    public $tr_attrs;       // assoc array.  keys:  bgcolor, ...
    public $td_attrs;       // assoc array.  keys:  bgcolor, ...
    public $row_title_field;
    public $row_title_mask = '%s';
    public $row_id_field;
    public $row_id_mask = '%s';
    public $timestamptz_col_names = array( 'timest' => 1, 'timestamp' => 1, 'datetime' => 1 );
    
    function odd_even_style_css( $row_color = '#eeeeee', $row_color_alt = '#dddddd' ) {
        return "tr.odd { background-color: $row_color; }\n" .
               "tr.even { background-color: $row_color_alt; }\n";
    }
    
    function row( $row, $cols, $cnt ) {

        if( is_object( $row ) ) {
            $row = get_object_vars( $row );
        }
        $tr_attrs = $this->_get_attrs_buf( $this->tr_attrs );
        $td_attrs_orig = $this->_get_attrs_buf( $this->td_attrs );
        
        $odd_or_even = $cnt % 2 == 0 ? 'even' : 'odd';
        $tr_attrs = "class='cnt-$cnt $odd_or_even' " . $tr_attrs;
        $tr_id = $this->row_id_field ? sprintf( ' id = "%s"', sprintf( $this->row_id_mask, @$row[$this->row_id_field] ) ) : '';
        $tr_title = $this->row_title_field ? sprintf( ' title = "%s"', sprintf( $this->row_title_mask, @$row[$this->row_title_field] ) ) : '';
        
        $buf = "<tr $tr_attrs$tr_id$tr_title>\n";
        if( !$cols ) {
            $cols = array_keys( $row );
        }

        foreach( $cols as $k => $key ) {
            $td_attrs = $td_attrs_orig;  // set/reset.

            // allow each column to have a callback.
            if( is_array( $key )) {
                $cb = @$key['cb_format'];
                $val = @$row[$k];
                $val = is_callable( $cb ) ? call_user_func( $cb, $val, $row ) : null;
                $key = $k;
            }
            else {
                $val = @$row[$key];
            }
            
            // allow each row value to have its own callback
            if( is_array( $val ) ) {
                $cb = @$val['cb_format'];
                $val = @$val['value'];
                $val = is_callable( $cb ) ? call_user_func( $cb, $val, $row ) : null;
            }
            
            if( is_numeric( $val ) && @$this->timestamptz_col_names[$key] ) {
                $val = self::format_timestamp( $val );
            }
            
            if( is_bool( $val )) {
                $val = $val ? 'Yes' : 'No';
            }
            
            $td_attrs = "class='td-$key' " . $td_attrs;
            if( $this->right_align_numeric && is_numeric( $val )) {
                $td_attrs .=  " align='right'";
            }
            $buf .= "<td $td_attrs>" . $val . "</td>\n";
        }
        $buf .= "</tr>\n";

        return $buf;
    }
    
    static public function format_timestamp( $val ) {
        $val_t = date('Y-m-d H:i:s T', $val );
        return "<time datetime='$val_t' epoch='$val'>$val_t</time>";
    }
    
    function header_row( $row ) {
        $attrs = $this->_get_attrs_buf( $this->header_attrs );
        $buf = "<thead><tr class='tr-header' $attrs>\n";
        foreach( $row as $key => $val ) {
            if( is_array( $val ) ) {
                $val = @$val['text'];
            }
            $buf .= "<th class='td-header-$key'>" . $val . "</th>\n";
        }
        $buf .= "</tr></thead>\n";
        return $buf;
    }
    
    private function _get_attrs_buf( $attrs ) {
        
        $table_attrs = '';
        
        if( is_array( $attrs ) ) {
            $cnt = 0;
            foreach( $attrs as $attr => $val ) {
                $table_attrs .= ($cnt++ == 0 ? '' : ' ' ) . sprintf( '%s="%s"', $attr, $val );
            }
        }

        return $table_attrs;
    }
    
    function table_with_header( $rows, $header, $cols = null ) {
        
        $attrs = $this->_get_attrs_buf( $this->table_attrs );
        
        $buf = "<table $attrs>\n";
        
        $buf .= $this->header_row( $header );
        
        $buf .= "<tbody>\n";
        
        $cnt = 1;
        foreach( $rows as $row ) {
            $buf .= $this->row( $row, $cols, $cnt ++ );
        }
        $buf .= "</tbody>\n";
        
        $buf .= "</table>\n";
        return $buf;
    }

    function table( $rows, $cols = null ) {
        
        $attrs = $this->_get_attrs_buf( $this->table_attrs );
        
        $buf = "<table $attrs>\n";

        $cnt = 1;        
        foreach( $rows as $row ) {
            $buf .= $this->row( $row, $cols, $cnt ++ );
        }
        
        $buf .= "</table>\n";
        return $buf;
    }
 
    function table_from_assoc_array( $arr, $colmap = array(), $include_unmapped = true ) {
        
        $rows = array( );
        if( @count( $colmap ) && !$include_unmapped ) {

            foreach( $colmap as $key => $label ) {
                
                $val = @$arr[$key];
                
                if( is_array( $label ) ) {
                    $label_tmp = @$label['label'];
                    $label['value'] = $val;
                    $val = $label;
                    $label = $label_tmp;
                }
                
                $label = "<span class='rowlabel'>$label</span>";
                $rows[] = array( $label, $val );
            }

            return $this->table( $rows );
            
        }
        
        foreach( $arr as $key => $val ) {
            $label = @$colmap[$key];
            if( !$label && !$include_unmapped ) {
                continue;
            }
            $label = $label ? $label : $key;
            $label = "<span class='rowlabel'>$label</span>";
            $rows[] = array( $label, $val );
        }
        
        return $this->table( $rows );
        
    }
 
 
    function table_from_object( $obj, $colmap = array(), $include_unmapped = true ) {
        
        $attrs = get_object_vars( $obj );
        return $this->table_from_assoc_array( $attrs, $colmap, $include_unmapped );
        
    }
    
    
}