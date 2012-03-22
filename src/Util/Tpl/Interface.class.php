<?php
/**
 * 
 * 模版类接口
 * @author welefen
 *
 */
interface Fl_Tpl_Interface {
	
	public function getToken(Fl_Token &$instance);
	
	public function checkHasOutput($tpl, Fl_Base &$instance);
	
	public function compress($tpl, Fl_Base &$instance);
}