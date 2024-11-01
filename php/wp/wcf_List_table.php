<?php
namespace wcf_coolform;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * creates a concrete form admin page.
 * 
 * Created by thomas
 * 
 */
class Wp_List_Table_Creator {

    var $id;
    var $cols2show;

    public function createFormPage($id) {
        $this->init($id);
        $this->loadHeader();
        $this->list_table_page();
        $this->loadColChanger();
        $this->closeForm();
        $this->loadFooter();
    }

    function init($id) {
        $this->id = $id;
    }

    function getFormFields() {
        $ini = new WcfIniFilesHandler($this->id);
        return $ini->readFormFieldsIni();
    }

    function loadColChanger() {
        $ini = new WcfIniFilesHandler($this->id);
        $html = new WcfHtmlHelper();
        $cols2show = $ini->readTableIniFile();
        $this->cols2show = $this->getFormFields();
        $i = 0;
        $ret = "";
        $block = "";
        foreach ($this->cols2show as $col => $name) {
            $i++;
            $match = false;
            foreach ($cols2show as $showcol) {
                if ($showcol === $col) {
                    $match = true;
                }
            }
            if ($match) {
                $tr = $html->addTrTag($html->addTdTag($name) . $html->addTdTag("<input type='checkbox' name='show_" . $col . "' value='" . $col . "' checked>"));
            } else {
                $tr = $html->addTrTag($html->addTdTag($name) . $html->addTdTag("<input type='checkbox' name='show_" . $col . "' value='" . $col . "'>"));
            }
            $block .= $tr;
            if ($i > 5) {
                $ret .= $html->addTdTag($html->addTableTag($block));
                $block = "";
                $i = 0;
            }
        }
        if (!empty($block)) {
            $ret .= $html->addTdTag($html->addTableTag($block));
        }
        echo $html->addH2Tag(tr('show_cols'), "");
        echo $html->addTableTag($html->addTrTag($ret));
        echo '<input id="unique_form_id" type="hidden" name="unique_form_id" value="'.$this->id.'">';
        //unique_form_id
        echo $html->addPTag("<input type='submit' name='change_table_columns_shown' value='" . tr('change') . "'>");
    }

    function closeForm() {
        echo "</form>";
    }

    function loadHeader() {
        $header = getPathFormPageHeaderTemplate();
        $contentHeader = file_get_contents($header);
        $handler = new WCFSQLHandler();
        $form = $handler->getFormular($this->id);
        $contentHeader = str_replace("[formname]", $form[0]['form_name'], $contentHeader);
        $contentHeader = str_replace("[FORM_ID]", $this->id, $contentHeader);
        $contentHeader = str_replace("[PAGE_URL]", get_site_url(), $contentHeader);
        $contentHeader = str_replace("[FORM_PAGE_URL]", get_page_link($form[0]['post_id']), $contentHeader);
        $contentHeader = str_replace("[WCF_LOGO]", WCF_LOGO, $contentHeader);

        echo $contentHeader;
    }

    function loadFooter() {
        $footer = getPathFormPageFooterTemplate();
        echo file_get_contents($footer);
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page() {
        $listTable = new Wcf_List_Table();
        $listTable->setFormId($this->id);
        $listTable->prepare_items();
        ?>
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <?php
        $listTable->display();
    }

}

// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Wcf_List_Table extends \WP_List_Table {

    var $formId;
    var $colHeadlines;
    var $specialCol = "column0";
    var $max_cols = 7;

    /**     * ***********************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     * ************************************************************************* */
    function __construct() {
        global $status, $page;

        //Set parent defaults
        parent::__construct(array(
            'singular' => 'singular', //singular name of the listed records
            'plural' => 'plural', //plural name of the listed records
            'ajax' => false        //does this table support ajax?
        ));
    }

    public function setFormId($formId) {
        $this->formId = $formId;
        $ff = new WCFNewFormFormHandler();
        $ini = new WcfIniFilesHandler($formId);
        $fields = $ini->readFormFieldsIni();
        $cols2show = $ini->readTableIniFile();
        $this->colHeadlines = array();
        $this->colHeadlines['cb'] = 'cb';
        $this->colHeadlines['id'] = 'id';
        $i = 0;
        $first = true;
        foreach ($fields as $key => $value) {
            $i++;
            $col = $key;
            $name = $value;
            //list($col, $name) = split("=", trim($line));
            foreach ($cols2show as $value) {
                if ($col === $value) {
                    $this->colHeadlines[$col] = $name;
                    if ($first) {
                        $this->specialCol = $col;
                        $first = false;
                    }
                }
            }
        }
    }


    var $counter = 0;

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->process_bulk_action();
        $data = $this->table_data();
        usort($data, array(&$this, 'sort_data'));
        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns() {
        return $this->colHeadlines;
    }

    function get_bulk_actions() {
        $actions = array(
            'delete' => 'Delete'
        );

        return $actions;
    }

    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="%1$s[]" value="%2$s" />',
                /* $1%s */ $this->_args['singular'], //Let's simply repurpose the table's singular label ("video")
                /* $2%s */ $item['id']             //The value of the checkbox should be the record's id
        );
    }

    function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            $sql = new WCFSQLHandler();
            // das ist noch nicht korrekt
            wcflog("singular: " . wcfget('singular'));
            $arr = wcfget('singular');
            $count = array_count_values($arr);
            for ($i = 0; $i < $count;$i++) {
                wcflog("element of the array: " . $arr[$i]);
                $sql->deleteFormularData($arr[$i]);
            }
            if (wcfget('id') !== null) {
                $sql->deleteFormularData(wcfget('id'));
            }
        }
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns() {
        $keys = array_keys($this->colHeadlines);
        $ret = array();
        foreach ($keys as $key) {
            $ret[$key] = array($key,false);
        }
        return $ret;
    }

    private function table_data() {
        $data = array();
        $fd = new WCFSQLHandler();
        $keys = array_keys($this->colHeadlines);
        $cols = "";
        $i = 0;
        $max = $this->max_cols + 2;
        foreach ($keys as $key) {
            $i++;
            if ($key == "cb") {
                continue;
            }
            if ($cols != "") {
                $cols .= ",";
            }
            $cols .= $key;
            if ($i === $max) {
                break;
            }
        }
        $result = $fd->getFormData($this->formId, $cols);
        return $result;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name) {

        if ($this->specialCol === $column_name) {
            //Build row actions
            if (WP_CF_PRO) {
            $actions = array(
                'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
                'delete' => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
                'form_download' => sprintf('<a href="' . get_site_url() . '?page=%s&action=%s&download_concrete_form=%s">Download</a>', $_REQUEST['page'], 'form_download', $item['id']),
            );
            } else {
                $actions = array(
                'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
                'delete' => sprintf('<a href="?page=%s&action=%s&id=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
            );
            }
            //Return the title contents
            return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
                    /* $1%s */ $item[$column_name],
                    /* $2%s */ $item['id'],
                    /* $3%s */ $this->row_actions($actions)
            );
        } else {
            return $item[$column_name];
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data($a, $b) {
        // Set defaults
        $orderby = 'column0';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if (!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if (!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        if (isset($a[$orderby]) && isset($b[$orderby])) {
            $result = strcmp($a[$orderby], $b[$orderby]);
        }  else {
            return 0;
        }
        if ($order === 'asc') {
            return $result;
        }
        return -$result;
    }

}

class WcfFormActions {

    /**
     * called on csv export.
     * 
     * @param type $formId
     */
    function downloadFormDataAsCSV($formId) {
        $formId = filter_var($formId, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        $ff = new WCFNewFormFormHandler();
        $ini = new WcfIniFilesHandler($formId);
        $keyVals = $ini->readFormFieldsIni();
        $handler = new WCFSQLHandler();
        $rows = $handler->getFormData($formId, "*");
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . "formdata.csv");
        header('Content-Transfer-Encoding: utf-8');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        ob_clean();
        flush();
        $first = true;
        foreach ($rows as $row) {
            if ($first) {
                echo "Timestamp;" . implode(";", array_values($keyVals));
                echo "\n";
                $first = false;
            }
            echo $row['ts'] . ";";
            foreach ($keyVals as $key => $val) {
                echo $row[$key] . ";";
            }
            echo "\n";
        }
        exit();
    }

    /**
     * 
     * @param type $formId
     */
    function downloadConcreteForm($file, $type) {
        header('Content-Description: File Transfer');
        if ($type === "docx") {
            //header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        } else {
            //header('Content-Type: application/zip');
        }
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment; filename=formular.' . $type);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        // file wieder loeschen
        unlink($path);
        exit();
    }

    /**
     * 
     * @param type $formId
     */
    function downloadForm($formId) {
        $path = getPathWcfForms() . $formId . "/wordfile";
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . basename($path));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        ob_clean();
        flush();
        readfile($path);
        // file wieder loeschen
        unlink($path);
        exit();
    }

}
