<?php
/**
 * Typecho Xcache缓存插件，修改自@Shion的FileCache
 *
 * @package TypechoXcache
 * @author Misaka
 * @version 1.0.2
 * @link http://www.aneko.net
 */
class TypechoXcache_Plugin implements Typecho_Plugin_Interface {
	/* 激活插件方法 */
	private static $defaultPrefix = 'Typecho_cache_';
	private static $defaultCacheTTL = 600;
	public static function activate() {
		// 触发机制
		Typecho_Plugin::factory('index.php')->begin = array('TypechoXcache_Plugin', 'getCache');
		Typecho_Plugin::factory('index.php')->end = array('TypechoXcache_Plugin', 'setCache');
		Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('TypechoXcache_Plugin', 'finish');
		Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('TypechoXcache_Plugin', 'clearCache');

		// $cache_dir = './usr/plugins/TypechoXcache/Cache/';
		// if (!file_exists($cache_dir)) {
		// 	if (mkdir($cache_dir, 0777) && chmod($cache_dir, 0777)) {
		// 		return ('建立缓存文件夹成功, 插件成功激活!');
		// 	} else {
		// 		throw new Typecho_Plugin_Exception('建立缓存文件夹失败, 请检查权限设置！');
		// 	}
		// } else {
		// 	// 这边 return 一个在顶部的提示
		// 	return ('缓存文件夹已存在, 插件成功激活!');
		// }
		// Typecho_Plugin::factory('admin/menu.php')->navBar = array('TypechoXcache_Plugin', 'render');
		if (!function_exists('xcache_count')) {
			throw new Typecho_Plugin_Exception('开启插件失败，缺少xcache环境！');
		} else {
			return '开启成功！';
		}
		// xcache_count();
	}
	/* 禁用插件方法 */
	public static function deactivate() {
		$pluginOpts = Typecho_Widget::widget('Widget_Options')->plugin('TypechoXcache');
		if ($pluginOpts->clearCacheAfterDisable == 'true') {
			xcache_unset_by_prefix($pluginOpts->$prefix);
			return ('缓存清空，成功关闭');
		}
		return ('成功关闭');

	}

	/* 插件配置方法 */
	public static function config(Typecho_Widget_Helper_Form $form) {
		/* 缓存前缀 */
		$prefix = new Typecho_Widget_Helper_Form_Element_Text('prefix', null, self::$defaultPrefix, _t('缓存前缀'), '请不要随意更改！修改该值会导致缓存丢失！');
		// 增加一条规则，不可为空
		$prefix->addRule('required', _t('缓存前缀不能为空！'));
		$form->addInput($prefix);

		/* 缓存时间 */
		$cacheTTL = new Typecho_Widget_Helper_Form_Element_Text('cacheTTL', null, '暂时没什么卵用', _t('缓存时间'), '单位为秒');
		// 增加一条规则，不可为空
		// $prefix->addRule('required', _t('缓存时间不能为空！'));
		$form->addInput($cacheTTL);

		/* 禁用插件时是否清空缓存 */
		$clearCacheAfterDisable = new Typecho_Widget_Helper_Form_Element_Radio(
			'clearCacheAfterDisable',
			array(
				'true' => '是',
				'false' => '否',
			),
			'false',
			_t('禁用插件时是否清空缓存')
		);
		$form->addInput($clearCacheAfterDisable);
	}

	/* 个人用户的配置方法 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

	public static function getCache() {
		// return false;
		// clearstatcache();
		$request = new Typecho_Request;
		if ($request->isPost()) {
			// 提交评论时，即有新评论收到时会先清空本文章缓存
			$file_path = (str_replace('/comment', '', $request->getPathinfo()));
			$file_name = md5($file_path);
			// xcache_set('TEST', $file_path);
			// xcache_set('TEST2', $file_name);
			xcache_unset('Typecho_cache_' . $file_name);
			// 	if (file_exists($file_path)) {
			// 		@chmod($file_path, 0777);
			// 		@unlink($file_path);
			// 	}
			// 	// echo $file_path;
			// echo 1;
		} else {
			// $file_name = md5($request->getPathinfo());
			// var_dump($request);

			// 评论
			$file_name = md5(preg_replace('/(.*)(\/comment.*)/i', '$1', $request->getPathinfo()));
			if (xcache_isset('Typecho_cache_' . $file_name)) {
				// xcache_set('Typecho_cache_' . $file_name, ob_get_contents(), 600);
				echo xcache_get('Typecho_cache_' . $file_name);
				exit();
			}
			// ob_start();
			// echo '12313';

			// 	$file_path = self::getPath($request->getPathinfo());
			// 	if (file_exists($file_path)) {
			// 		if (self::isValid($file_path)) {
			// 			$handle = @fopen($file_path, 'rb');
			// 			if (@flock($handle, LOCK_SH | LOCK_NB)) {
			// 				fpassthru($handle);
			// 				flock($handle, LOCK_UN);
			// 				fclose($handle);
			// 				exit;
			// 			}
			// 			echo "string2";
			// 			fclose($handle);
			// 		} else {
			// 			@chmod($file_path, 0777);
			// 			@unlink($file_path);
			// 			echo "string3";
			// 		}
			// 	}
		}
	}

	public static function clearCache($contents, $edit) {
		// exit();
	}
	public static function setCache() {
		$request = new Typecho_Request;
		$file_name = md5($request->getPathinfo());
		// $file_path = self::getPath($request->getPathinfo());
		// 写入xcache 默认10分钟
		if (!xcache_isset($file_name)) {
			xcache_set('Typecho_cache_' . $file_name, ob_get_contents(), 600);
		}
		// var_dump($request->getPathinfo());
		// if (!file_exists($file_path)) {

		// 	$handle = @fopen($file_path, 'wb');
		// 	if (@flock($handle, LOCK_EX | LOCK_NB)) {
		// 		// xcache_set('tmp', ob_get_contents());
		// 		fwrite($handle, ob_get_contents() . "<!-- This Is A Cache File Created At " . date("Y-m-d H:i:s", time() + 28800) . " Power By http://www.shionco.com -->");
		// 		flock($handle, LOCK_UN);
		// 	}
		// 	fclose($handle);
		// }
	}

	// public static function getPath($pathinfo) {
	// 	// $cache_dir = './usr/plugins/TypechoXcache/Cache/';
	// 	// $file_name = md5($pathinfo) . '.tmp';
	// 	// $cache_dir .= substr($file_name, 0, 1) . '/';
	// 	// if (!file_exists($cache_dir)) {
	// 	// 	@mkdir($cache_dir, 0777);
	// 	// 	@chmod($cache_dir, 0777);
	// 	// }
	// 	// return $cache_dir . $file_name;
	// }

	public static function isValid($file_path) {
		$mtime = filemtime($file_path);
		if (time() - $mtime > 3600) {
			return false;
		} else {
			return true;
		}
	}

	public static function finish($data) {
		// xcache_set('1231', var_dump($data));
		// $cid = $data->stack[0]['cid'];
		// $routingTable = Typecho_Widget::widget('Widget_Options')->routingTable;
		// $db = Typecho_Db::get();
		// $row = $db->fetchRow($db->select('slug')->from('table.contents')->where('cid = %s', $cid));
		// $slug = $row['slug'];
		// $pathinfo = str_replace('[slug]', $slug, $routingTable[0]['post']['url']);
		// // 获取评论目标地址
		// $file_name = md5($pathinfo);
		// xcache_set('TEST', $pathinfo);
		// xcache_set('TEST2', $file_name);
		// xcache_unset('Typecho_cache_' . $file_name);
		// $file_path = self::getPath($pathinfo);
		// if (file_exists($file_path)) {
		// 	@chmod($file_path, 0777);
		// 	@unlink($file_path);
		// }
	}
}
