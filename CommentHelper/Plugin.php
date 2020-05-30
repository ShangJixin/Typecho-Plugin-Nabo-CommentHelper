<?php
/**
 * 南博助手 - 消息推送 的配套插件，使评论动作可以响应到南博插件上。运行此插件需要安装南博的原插件！
 * 
 * @package CommentHelper
 * @author 尚寂新
 * @version 1.0
 * @dependence 17.11.15
 */
class CommentHelper_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Feedback')->finishComment = array('CommentHelper_Plugin', 'send');
        Typecho_Plugin::factory('Widget_Service')->sendMail = array('CommentHelper_Plugin', 'sendMail');
        return _t('插件已经激活，插件正常运行需要安装南博的原插件！');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){
        $form->addInput(new Typecho_Widget_Helper_Form_Element_Textarea('template', NULL, "你收到了关于文章《{title}》来自 {user} 的评论\n\n以下是评论详情:\n\n{text}", _t('消息正文模版'),_t('可选参数：<code>{title}</code> <code>{user}</code> <code>{text}</code> <code>{url}</code><br><br><span style="color:red">需要安装南博的原消息推送插件，本插件才可以正常运行！<br>下载地址：<a href="https://github.com/kraity/Messages/releases" target="_blank">https://github.com/kraity/Messages/releases</a></span>')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 异步回调
     * 
     * @access public
     * @param int $commentId 评论id
     * @return void
     */
    public static function sendMail($commentId)
    {
        $options = Helper::options();
        $pluginOptions = $options->plugin('CommentHelper');
        $comment = Helper::widgetById('comments', $commentId);
        if (!$comment->have()) {
            return;
        }
        if(!class_exists('Messages_Plugin')){
            return;
        }

        $push_content = str_replace(array('{user}', '{title}', '{url}', '{text}'),
            array($comment->author, $comment->title, $comment->permalink, $comment->text), $pluginOptions->template);
        Messages_Plugin::send($push_content);
    }

    /**
     * 评论回调
     *
     * @param $comment
     */
    public static function send($comment)
    {
        Helper::requestService('sendMail', $comment->coid);
    }
}
