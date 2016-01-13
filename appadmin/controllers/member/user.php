<?php

class user extends MY_Controller {

    protected static $common_config;
    protected $table_name;
    public function __construct() {
        parent::__construct();
        $this->load->library('form');
        $this->dbr = $this->load->database("dbr", TRUE);
        $this->load->config("common_config", TRUE);
        self::$common_config = $this->config->item('common_config');
        $this->load->model("member/official_accounts_model", "official_accounts_model");
        $this->table_name = "user";
    }

    public function index() {
        $this->user_list();
    }
    
    public function user_list() {
    	$this->load->library('form');
        $page = $this->input->get('page');
        $page = max(intval($page),1);
        $dosearch = $this->input->get('dosearch');
        
        $search_arr['status']=1;
        $where_array[] = "status = 1";
        
        if($dosearch == 'ok'){
        	
        	$search_filed=array(
        		'user_type'=>array(
        			1=>'type=1',
        			2=>'type=2',
        		),
        		'user_level'=>array(
        			1=>'level=1',
        			2=>'level=2',
        		)
        	);
        	
        	if(intval($this->input->get('user_type_id'))!=''){
        		$user_type_id=$this->input->get('user_type_id');
        		if($search_filed['user_type'][$user_type_id]!=''){
        			$where_array[]=$search_filed['user_type'][$user_type_id];
        		}
        	}
        	if(intval($this->input->get('user_level_id'))!=''){
        		$user_level_id=$this->input->get('user_level_id');
        		if($search_filed['user_level'][$user_level_id]!=''){
        			$where_array[]=$search_filed['user_level'][$user_level_id];
        		}
        	}
        	
            $phone = trim($this->input->get('phone'));
            $search_arr['phone'] = $phone;
            if($phone != ''){
                $where_array[] = "phone = '{$phone}' ";
            }
            $keywords = trim($this->input->get('keywords'));
            $search_arr['keywords'] = $keywords;
            if($keywords != ''){
                $where_array[] = "wx_name like '%{$keywords}%' or nick_name like '%{$keywords}%' ";
            }
        }
        if(is_array($where_array) and count($where_array) > 0) {
            $where = ' WHERE '.join(' AND ',$where_array);
        }
        $pagesize  = 20;
        $offset    = $pagesize*($page-1);                                                                                     
        $limit     = " LIMIT $offset,$pagesize";
		$order     = " ORDER BY uid DESC";
        $sql_ct    = "SELECT uid FROM $this->table_name $where";
        $query     = $this->dbr->query($sql_ct);
        $log_num   = $query->num_rows();
        $pages     = pages($log_num, $page, $pagesize);
        $sql       = "SELECT uid, wx_name, nick_name, cmpy_name, phone, email, url, type, level, zfb_account, ctime, utime FROM $this->table_name $where $order $limit";
        log_message('debug', '[************************************]'. __METHOD__ .':'.__LINE__.' user_list sql [' . $sql .']');
        $result    = $this->dbr->query($sql);
        log_message('debug', '[************************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($result) .']');
        $list_data = $result->result_array();
        
        // 获取详情
        $res_content = array();
        foreach($list_data as $item_user) {
        	$uid = $item_user['uid'];
        	$type = $item_user['type'];
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' curr uid [' . $uid .']');
//         	$sql_ofc = "select oaid,uid,ofc_account,nick_name from official_accounts ";
        	$ofc_list = $this->official_accounts_model->get_ofc_list_by_uid($uid);
        	log_message('debug', '[******************************]'. __METHOD__ .':'.__LINE__.' result [' . json_encode($ofc_list) .']');
        	if ($type == 2) { //类型：1广告主，2媒体主
        		if (count($ofc_list) > 0) {
        			$ofc_name = $ofc_list[0]['nick_name']; // 暂时取昵称
        		} else {
        			$ofc_name = '未绑定';
        		}
        	} else {
        		$ofc_name = ''; // 广告主没有公众号的概念
        	}
        	$item_user['ofc_name'] = $ofc_name;
        	$res_content[] = $item_user;
        	
        }
        
        
        $user_type_arr = self::$common_config['user_type'];
        $search_arr['user_type_sel']=$this->form->select($user_type_arr,$user_type_id,'name="user_type_id"','选择用户类型');
        $user_level_arr = self::$common_config['user_level'];
        $search_arr['user_level_sel']=$this->form->select($user_level_arr,$user_level_id,'name="user_level_id"','选择用户等级');
        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('user_type_arr', $user_type_arr);
        $this->smarty->assign('user_level_arr', $user_level_arr);
        $this->smarty->assign('list_data', $res_content);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true'); 
        $this->smarty->display("member/user_list.html");
    }

    public function user_add() {
        exit("todo");
        $sex_radio = Form::radio(self::$common_config['sex'], 0,'name="info[sex]" id="sex"');
        $status_radio = Form::radio(self::$common_config['status'], 1,'name="info[status]" id="status"');
        $this->smarty->assign('sex_radio', $sex_radio);
        $this->smarty->assign('status_radio', $status_radio);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/user_add.html");
    }

    public function user_add_do() {
        $info = $this->input->post('info');
        //$this->load->library('mcrypt_3des');
        $info['create_time'] = time();
        //$info['umobile'] = $this->mcrypt_3des->en3des(trim($info['umobile']), array('create_time'=> $info['create_time']));
        //print_r($info);exit;
        if ($info['uname'] != '' && $info['umobile'] != '') {
            $insert_query = $this->db->insert_string($this->table_name, $info);

            $this->db->query($insert_query);
            show_tips('操作成功','','','add');
        } else {
            show_tips('数据不完整，请检测');
        }
    }

    public function user_view() {
        $this->load->library('form');
        $aid = intval($this->input->get('id'));
        $sql = "SELECT uid, wx_name, nick_name, cmpy_name, phone, email, url, type, level, zfb_account, ctime, utime FROM {$this->table_name} WHERE uid={$aid}";
        $result = $this->db->query($sql);
        $info = $result->row_array();
        //$this->load->library('mcrypt_3des');
        //$cfg['create_time'] = $info['create_time'];
        //$info['umobile'] = $this->mcrypt_3des->de3des($info['umobile'], $cfg);
        $type_radio = Form::radio(self::$common_config['user_type'], $info['type'],'name="info[type]" id="type"');
        $level_radio = Form::radio(self::$common_config['user_level'], $info['level'],'name="info[level]" id="level"');
        $this->smarty->assign('type_radio', $type_radio);
        $this->smarty->assign('level_radio', $level_radio);
        $this->smarty->assign('info', $info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/user_view.html");
    }
    
    public function user_edit() {
        $this->load->library('form');
        $aid = intval($this->input->get('id'));
        $sql = "SELECT uid, wx_name, nick_name, cmpy_name, phone, email, url, type, level, zfb_account, ctime, utime FROM {$this->table_name} WHERE uid={$aid}";
        $result = $this->db->query($sql);
        $info = $result->row_array();
        
        $type_radio = Form::radio(self::$common_config['user_type'], $info['type'],'name="info[type]" id="type" disabled');
        $level_radio = Form::radio(self::$common_config['user_level'], $info['level'],'name="info[level]" id="level"');
        $this->smarty->assign('type_radio', $type_radio);
        $this->smarty->assign('level_radio', $level_radio);
        $this->smarty->assign('info', $info);
        $this->smarty->assign('show_dialog','true');
        $this->smarty->assign('show_validator','true');
        $this->smarty->display("member/user_edit.html");
    }

    public function user_edit_do() {
        $cfg = $this->input->post('cfg');
        if($cfg['uid'] < 1) {show_tips('参数异常，请检测');} else {$where = "uid={$cfg['uid']}";}
        $info = $this->input->post('info');
		if ($_FILES['pic']['name'] != "") {
            $this->load->library('oss');
            $pic_ret = $this->oss->upload('pic', array('dir'=>'url'));
            if (isset($pic_ret['error_code']) && intval($pic_ret['error_code'])) {
                show_tips($pic_ret['error_code']. ":" . $pic_ret['error']);
            }
            $info['url'] = $pic_ret;
        }
        $info['utime'] = time();
        //log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' info sql [' . json_encode($info) .']');
        $query = $this->db->update_string($this->table_name, $info, $where);
        if($this->dbr->query($query)){
			$pre = self::get_user_key();
			$key = $pre.$cfg['uid'];
			$this->hMset($key, $info);
            show_tips('操作成功','','','edit');
        }else{
            show_tips('操作异常，请检测');
        }
    }

	private static function get_user_key() {
        return 'hunshui::user::info::';
		//return 'user_';
	}

    public function user_locked() {
        $this->locked();
    }

    public function user_status() {
        $this->status();
    }

	public function user_del_one_ajax() {
		$aid = intval($this->input->get('uid'));
        if($aid>0) {
            //$del_query = "DELETE FROM ci_user WHERE id={$aid}";
            $del_query = "UPDATE {$this->table_name}  SET `status`= 2 WHERE uid={$aid}";
            $this->db->query($del_query);
            echo 1;
        } else {
            echo 0;
        }
	
	}
	
	public function user_upgrade_one() {
		if(intval($_POST['dosubmit'])==1) {
			$uid=$this->input->post('uid');
			if($uid>0) {
				$sql = "UPDATE {$this->table_name}  SET `level`= 2 WHERE uid={$uid}";
				$this->db->query($sql);
				show_tips('操作成功',HTTP_REFERER);
			} else {
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}
	
	public function user_degrade_one() {
		if(intval($_POST['dosubmit'])==1) {
			$uid=$this->input->post('uid');
			if($uid>0) {
				$sql = "UPDATE {$this->table_name}  SET `level`= 1 WHERE uid={$uid}";
				$this->db->query($sql);
				show_tips('操作成功',HTTP_REFERER);
			} else {
				show_tips('参数有误，请重新提交');
			}
		} else {
			show_tips('操作异常');
		}
	}

	public function user_upgrade_one_ajax() {
		$aid = intval($this->input->get('uid'));
        if($aid>0) {
            $del_query = "UPDATE {$this->table_name}  SET `level`= 2 WHERE uid={$aid}";
            $this->db->query($del_query);
            echo 1;
        } else {
            echo 0;
        }
	
	}

	public function user_degrade_one_ajax() {
		$aid = intval($this->input->get('uid'));
        if($aid>0) {
            $del_query = "UPDATE {$this->table_name}  SET `level`= 1 WHERE uid={$aid}";
            $this->db->query($del_query);
            echo 1;
        } else {
            echo 0;
        }
	
	}
	
    public function user_delete() {
        if(intval($_POST['dosubmit'])==1) {
            $ids=$this->input->post('ids');
            if(is_array($ids) and count($ids) > 0) {
                $ids_str = join("','",$ids);
                $query   = "UPDATE {$this->table_name}  SET `status`= 2 WHERE uid in('{$ids_str}')";
                log_message('debug', '[******]'. __METHOD__ .':'.__LINE__.' user_delete sql [' . $query .']');
                $rs = $this->db->query($query);
                //$this->log->write($this->user_info['user_name'], $this->ip, $ids_str, $this->table_name . ":status", $rs);
                show_tips('操作成功',HTTP_REFERER);
            } else {
                show_tips('参数有误，请重新提交');
            }
        } else {
            show_tips('操作异常');
        }
    }
    public function sms_list() {
        $page=$this->input->get('page');
        $page = max(intval($page),1);
        $dosearch=$this->input->get('dosearch');
        if($dosearch=='ok'){
            $keywords=trim($this->input->get('keywords'));
            $search_arr['keywords']=$keywords;
            if($keywords!=''){
                $where_array[]="phone='{$keywords}'";                                                                         
            }                                                                                                                        
            if(is_array($where_array) and count($where_array)>0){
                $where=' WHERE '.join(' AND ',$where_array);
            }
        } 
        $pagesize = 10;
        $offset = $pagesize*($page-1);                                                                                               
        $limit = " LIMIT $offset,$pagesize";
        $sql_ct = "SELECT sid FROM user_sms $where";
        $query = $this->dbr->query($sql_ct);
        $log_num = $query->num_rows();
        $pages = pages($log_num, $page, $pagesize);
        $sql = "SELECT `sid`, `uid`, `phone`, `verifycode`, `identifier`, `status`, `valid`, `ip_long`, `ctime_keep`, `ctime`, `operate` FROM user_sms $where  ORDER BY ctime DESC $limit";
        $result = $this->dbr->query($sql);
        $list_data = $result->result_array();                                                                                        
        $this->smarty->assign('search_arr', $search_arr);
        $this->smarty->assign('list_data', $list_data);
        $this->smarty->assign('pages', $pages);
        $this->smarty->assign('show_dialog', 'true');
        $this->smarty->display("member/user_sms_list.html");
    }
}
