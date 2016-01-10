<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * bug管理model
 */
class Bugtrack_model extends CI_Model {
    
    private $dbr = null;
    function __construct() {
        parent::__construct();
        $this->dbr = $this->load->database('dbr',TRUE,FALSE);
        $this->load->library('http2');
    }
    
    /**
     * 查询出筛选条件下的数据
     * @param str $where 查询条件
     * @param str $limit 条数筛选 
     * @return int $data 分会符合条件二维数组
     */
    public function get_data_by_parm($where, $limit){
        $query_data = "SELECT `id`, `type`, `title`, `content`, `create_user`, `handle_user`, `priority`, `status`, `publish_time`, `resolve_time`, `is_deleted` FROM ci_bugtrack {$where} ORDER BY status ASC, priority ASC, resolve_time DESC {$limit}";
        $result_data = $this->dbr->query($query_data);
        $list_data = $result_data->result_array();
        // 格式化数据
        $list_data = $this->format_bugtrack_data($list_data);
        return $list_data;
    }
    
    /**
     * 格式化函数
     * ＠param array $list_data
     * @return array $format_list_data
     */
    private function format_bugtrack_data($list_data) {
    	$format_list_data = array();
    	foreach ($list_data as $item) {
    		$tmp_item = $item;
    		if ($item['status'] == 2 || $item['status'] == '2') {
	    		$delay_time = intval($item['resolve_time']) - intval($item['publish_time']);
    		} else {
    			$delay_time = time() - intval($item['publish_time']);
    		}
    		$format_time = $this->format_time($delay_time);
    		$tmp_item['format_time'] = $format_time;
    		$format_list_data[] = $tmp_item;
    	}
    	return $format_list_data;
    }
    
    /**
     * 格式化时间
     * ＠param array $span
     * @return array $str
     */
    private function format_time($span) {
    	$format_time = '';
    	if ($span < 60) {
    		$format_time = "刚刚";
    	} elseif ($span < 3600) {
    		$format_time = intval($span/60) . "分钟前";
    	} elseif ($span < 24*3600) {
    		$format_time = intval($span/3600)."小时前";
    	} else {
    		$format_time = intval($span/(24*3600))."天前";
    	}
    	
    	return $format_time;
    }
    
    
    /**
     * 计算出筛选条件下的数据的条数
     * @param int $where 查询条件
     * @return int $data 返回数据的条数
     */
    public function get_count_by_parm($where){
        $query_data = "SELECT count(id) as nums FROM ci_bugtrack {$where}";
        $result_data = $this->dbr->query($query_data);
        $row_data = $result_data->row_array();
        return $row_data['nums'];
    }
    
    /**
     * 查询出指定的编号数据
     * @param int $id 数据ID编号
     * @return int $data 返回数据内容
     */
    public function get_info_by_id($id){
        $query_data = "SELECT `id`, `type`, `title`, `content`, `create_user`, `handle_user`, `priority`, `status`, `publish_time`, `resolve_time`, `is_deleted` FROM ci_bugtrack WHERE id=?";
        $result_data = $this->dbr->query($query_data,array($id));
        $row_data = $result_data->row_array();
        if($row_data['id']>0){
            $result = $row_data;
        }else{
            $result = '';
        }
        return $result;
    }
    
    /**
     * 对要闻进行单条推荐或取消推荐
     * @param int $id 需要推荐的id
     * @return bool 是否执行成功 
     */
    public function one_sug($id, $op) {
		$info['type'] = 'recommend';
		$info['tid']  = $id;
		$info['op']   = $op;
		$param_str = http_build_query($info);
		$url = $this->mis_tweet['tweet'] . '?' . $param_str;
		$ret = $this->http2->get($url);
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }
        return false;
    }

    /**
     * 批量推荐
     * @param str $ids_str 需要推荐的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_sug($ids_str){
        //is_essence
        return $this->one_sug($ids_str);
    }
    
    /**
     * 批量取消推荐
     * @param str $ids_str 需要变更的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_clear_sug($ids_str){
        $ret = $this->http2->post($this->mis_tweet['tweet']['cancel_sug_url'], array('ds' => $ids_str));
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }

        return false;
    }

    /**
     * 执行单条删除或取消删除操作
     * @param str $ids_str 需要删除的id集合
     * @return bool 是否执行成功 
     */
    public function one_del($id, $op) {
		$info['type'] = 'delete';
		$info['tid']  = $id;
		$info['op']   = $op;	
		$param_str = http_build_query($info);
		$url = $this->mis_tweet['tweet'] . '?' . $param_str;
        $ret = $this->http2->get($url);
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }
        return false;
    }

    /**
     * 对指定编号数据进行删除
     * @param arr $ids 需要删除的数据的id，可以是单个id，也可以是id的数组
     * @return bool 返回执行结果
     */
    public function del_info($ids){
        if(is_array($ids) && count($ids)){
            $id_str=join("','",$ids);
        }else{
            $id_str=$ids;
        }
        $del_rule="UPDATE ci_bugtrack SET is_deleted = 0 WHERE id IN('{$id_str}')";
        if($this->db->query($del_rule)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 批量执行删除
     * @param str $ids_str 需要删除的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_del($ids_str){
        return $this->one_del($ids_str);
    }
    
    /**
     * 批量取消删除
     * @param str $ids_str 需要删除的id集合
     * @return bool 是否执行成功 
     */
    public function tweet_clear_del($ids_str){
        $ret = $this->http2->post($this->mis_tweet['tweet']['cancel_del_url'], array('ds' => $ids_str));
        $data = json_decode($ret, true);
        if ($data['errno'] == 0) {
            return true;
        }

        return false;
    }
    
    
    /**
     * 向数据表中写入一行数据
     * @param arr $info 需要插入的数据
     * @param bool 是否成功执行
     */
    public function create_info($info){
        $insert_query=$this->db->insert_string('ci_bugtrack',$info);
        if($this->db->query($insert_query)){
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 对数据表中的单行数据进行修改
     * @param arr $info 需要修改的键值对
     * @param int $id 被修改的id编号
     * @return bool 是否执行成功 
     */
    public function update_info($info,$id){
        $where="id={$id}";
        $update_rule=$this->db->update_string('ci_bugtrack', $info, $where); 
        if($this->db->query($update_rule)){
            return true;
        }else{
            return false;
        }
    }
}
/* End of file this file */
?>
