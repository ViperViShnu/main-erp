<?php

/**
 * Description of Admin_Controller
 *
 * @author pc mart ltd
 */
class Admin_Controller extends MY_Controller
{
    private $_current_version;

    function __construct()
    {
        parent::__construct();
        $this->load->model('common_model');
        $this->load->model('admin_model');

        $this->_current_version = $this->admin_model->get_current_db_version();

        if ($this->admin_model->is_db_upgrade_required($this->_current_version) && !$this->input->post('auto_update', true)) {
            if ($this->input->post('upgrade_database', true)) {
                $this->admin_model->upgrade_database();
            }
            include_once(APPPATH . 'views/admin/settings/db_update_required.php');
            die;
        }
        if (strpos($this->uri->uri_string(), 'login') === FALSE) {
            $this->session->set_userdata(array(
                'url' => $this->uri->uri_string()
            ));
        }
        //get all navigation data
        $all_menu = get_result('tbl_menu');
        $_SESSION['user_roll'] = $all_menu;
    
        //get user id from session
        $designations_id = $this->session->userdata('designations_id');
        $this->common_model->_table_name = 'tbl_user_role'; //table name
        $this->common_model->_order_by = 'user_role_id';
        // get user navigation by user id
        $user_menu = $this->common_model->select_user_roll($designations_id);
    
        $user_type = $this->session->userdata('user_type');
        if ($user_type != 1) {
            $restricted_link = array();
            foreach ($all_menu as $data1) {
                $duplicate = false;
                foreach ($user_menu as $data2) {
                    if ($data1->menu_id === $data2->menu_id) {
                        $duplicate = true;
                    }
                }
                if ($duplicate === false) {
                    $restricted_link[] = $data1->link;
                }
            }
            $exception_uris = $restricted_link;
        } else {
            $exception_uris = array();
        }
        $exception_uris = apply_filters('more_exception_uri', $exception_uris);
        $user_flag = $this->session->userdata('user_flag');
        if (!empty($user_flag)) {
            // if ($user_flag != '1') {
            //     $url = $this->session->userdata('url');
            //     redirect($url);
            // }
        } else {
            redirect('locked');
        }
    
        // url segment
        $uri = null;
        $a = $this->uri->segment(1) . '/' . $this->uri->segment(2);
        if ($a != 'admin/settings') {
            for ($i = 1; $i <= $this->uri->total_segments(); $i++) {
                $uri .= $this->uri->segment($i) . '/';
                $result = rtrim($uri, '/');
                if (in_array($result, $exception_uris) == true) {
                    redirect('404');
                }
            }
        }
        // $exists = $this->db->where(array('label' => 'master_gst_settings', 'status' => 2))->get('tbl_menu');
        // if($exists->num_rows() == 0) {
        //     // $this->createClientIdSecret();
        // }

        // $case = 'Pro';
        // $case = 'Basic-1';
        $case = 'Basic-2';

        switch ($case) {
            case 'Pro':
                $proArray = array(112, 116, 117, 118, 121, 122, 123, 124, 136);
                $proSettingMenus = $this->db->where_not_in('menu_id', $proArray)->where('status', 2)->get('tbl_menu');
                if($proSettingMenus->num_rows() != 0) {
                    $this->setPackageSettingMenus($proSettingMenus->result());
                }

                $settingMenu = $this->db->where(array('menu_id' => 25, 'label' => 'settings', 'link' => 'admin/settings', 'status' => 1))->get('tbl_menu');
                if($settingMenu->num_rows() != 0) {
                    $this->db->where('menu_id', 25)->update('tbl_menu', array('link' => 'admin/settings/system'));
                }
                break;

            case 'Basic-1':
                $basicArray1 = array(121, 122, 136);
                $basicSettingMenus1 = $this->db->where_not_in('menu_id', $basicArray1)->where('status', 2)->get('tbl_menu');
                if($basicSettingMenus1->num_rows() != 0) {
                    $this->setPackageSettingMenus($basicSettingMenus1->result());
                }

                $settingMenu = $this->db->where(array('menu_id' => 25, 'label' => 'settings', 'link' => 'admin/settings', 'status' => 1))->get('tbl_menu');
                if($settingMenu->num_rows() != 0) {
                    $this->db->where('menu_id', 25)->update('tbl_menu', array('link' => 'admin/settings/system'));
                }
                break;
            
            default:
                $basicArray2 = array(116, 117, 118, 123, 124, 136);
                $basicSettingMenus2 = $this->db->where_not_in('menu_id', $basicArray2)->where('status', 2)->get('tbl_menu');
                if($basicSettingMenus2->num_rows() != 0) {
                    $this->setPackageSettingMenus($basicSettingMenus2->result());
                }

                $this->db->where('menu_id', 25)
                    ->group_start()
                        ->where('label', 'settings')
                        ->where('status', 1)
                        ->group_start()
                            ->where('link', 'admin/settings')
                            ->or_where('link', 'admin/settings/system')
                        ->group_end()
                    ->group_end();

                $settingMenu = $this->db->get('tbl_menu');

                if($settingMenu->num_rows() != 0) {
                    $this->db->where('menu_id', 25)->update('tbl_menu', array('link' => 'admin/settings/payments'));
                }

                $this->db->where(array('menu_id' => 50, 'status' => 1))->update('tbl_menu', array('status' => 0));
                break;
        }

        $warehouse_products = $this->db->get('tbl_warehouses_products')->result();
        foreach($warehouse_products as $product) {
            if(!empty($product->product_id)) {
                $check = $this->items_model->check_by(array('saved_items_id' => $product->product_id), 'tbl_saved_items');
                if(empty($check)) {
                    $this->db->where('product_id', $product->product_id);
                    $this->db->delete('tbl_warehouses_products');
                }
            }
        }

    }

    public function createClientIdSecret() {
        $data = array(
            'label' => 'master_gst_settings',
            'link' => 'admin/settings/gst_settings',
            'icon' => 'fa fa-fw fa fa-money',
            'parent' => 25,
            'sort' => 38,
            'status' => 2,
        );
        $this->db->insert('tbl_menu', $data);
    }

    public function setPackageSettingMenus($menus) {
        $menuIds = array();
        foreach($menus as $menu) {
            $menuIds[] = $menu->menu_id;
        }

        if(!empty($menuIds)) {
            $this->db->where_in('menu_id', $menuIds)->update('tbl_menu', array('status' => 3));
        }
    }
}