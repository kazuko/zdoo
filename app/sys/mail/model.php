<?php
/**
 * The model file of mail module of RanZhi.
 *
 * @copyright   Copyright 2009-2018 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     mail 
 * @version     $Id: model.php 4145 2016-10-14 05:31:16Z liugang $
 * @link        http://www.ranzhi.org
 */
?>
<?php
class mailModel extends model
{
    public static $instance;
    public $mta;
    public $mtaType;
    public $errors = array();

    public function __construct($appName = '')
    {
        parent::__construct($appName);
        $this->app->loadClass('phpmailer', $static = true);
        $this->setMTA();
    }

    /**
     * Auto detect email config.
     * 
     * @param  string    $email 
     * @access public
     * @return object
     */
    public function autoDetect($email)
    {
        /* Split the email to username and domain. */
        list($username, $domain) = explode('@', $email);
        $domain = strtolower($domain);

        /*
         * 1. try to find config from the providers. 
         * 2. try to find the mx record to get the domain and then search it in providers.
         * 3. try smtp.$domain's 25 and 465 port, if can connect, use smtp.$domain.
         */
        $config = $this->getConfigFromProvider($domain, $username);
        if(!$config) $config = $this->getConfigByMXRR($domain, $username);
        if(!$config) $config = $this->getConfigByDetectingSMTP($domain, $username, 25);
        if(!$config) $config = $this->getConfigByDetectingSMTP($domain, $username, 465);
        if(!$config) 
        {
            $config = new stdclass();
            $config->username = $username;
            $config->host     = 'smtp.' . $domain;
            $config->auth     = 1;
            $config->port     = 25;
            $config->secure   = '';
        }

        /* Set default values. */
        $config->mta      = 'smtp';
        $config->fromName = isset($this->config->company->name) ? $this->config->company->name : '';
        $config->password = '';
        $config->debug    = 1;
        if(!isset($config->host)) $config->host = '';
        if(!isset($config->auth)) $config->auth = 1;
        if(!isset($config->port)) $config->port = '25';

        return $config;
   }

    /**
     * Try get config from providers.
     * 
     * @param  int    $domain 
     * @param  int    $username 
     * @access public
     * @return bool|object
     */
    public function getConfigFromProvider($domain, $username)
    {
        if(isset($this->config->mail->provider[$domain]))
        {
            $config = (object)$this->config->mail->provider[$domain];
            $config->mta      = 'smtp';
            $config->username = $username;
            $config->auth     = 1;
            if(!isset($config->port))   $config->port   = 25;
            if(!isset($config->secure)) $config->secure = '';
            return $config;
        }
        return false;
    }

    /**
     * Get config by MXRR.
     * 
     * @param  string    $domain 
     * @param  string    $username 
     * @access public
     * @return bool|object
     */
    public function getConfigByMXRR($domain, $username)
    {
        /* Try to get mx record, under linux, use getmxrr() directly, windows use nslookup. */
        if(function_exists('getmxrr'))
        {
            getmxrr($domain, $smtpHosts);
        }
        elseif(strpos(PHP_OS, 'WIN') !== false)
        {
            $smtpHosts = array();
            $result    = `nslookup -q=mx {$domain} 2>nul`;
            $lines     = explode("\n", $result);
            foreach($lines as $line)
            {
                if(stripos($line, 'exchanger')) $smtpHosts[] = trim(substr($line, strrpos($line, '=') + 1));
            }
        }

        /* Cycle the smtpHosts and try to find it's config from the provider config. */
        foreach($smtpHosts as $smtpHost)
        {
            /* Get the domain name from the hosts, for example: imxbiz1.qq.com get qq.com. */
            $smtpDomain = explode('.', $smtpHost);
            array_shift($smtpDomain);
            $smtpDomain = strtolower(implode('.', $smtpDomain));
            if($config = $this->getConfigFromProvider($smtpDomain, $username))
            {
                $config->username = "$username@$domain";
                return $config;
            }
        }

        return false;
    }

    /**
     * Try connect to smtp.$domain's 25 or 465 port and compute the config according to the connection result.
     * 
     * @param  string $domain
     * @param  string $username
     * @param  int    $port 
     * @access public
     * @return bool|object
     */
    public function getConfigByDetectingSMTP($domain, $username, $port)
    {
        $host = 'smtp.' . $domain;
        ini_set('default_socket_timeout', 3);
        ob_start();
        $connection = fsockopen($host, $port);
        ob_end_clean();
        if(!$connection) return false;
        fclose($connection); 

        $config->username = $username;
        $config->host     = $host;
        $config->auth     = 1;
        $config->port     = $port;
        $config->secure   = $port == 465 ? 'ssl' : '';

        return $config;
     }

    /**
     * Set MTA.
     * 
     * @access public
     * @return void
     */
    public function setMTA()
    {
        if(self::$instance == null) self::$instance = new phpmailer(true);
        $this->mta = self::$instance;
        $this->mta->CharSet = 'utf-8';
        $funcName = "set{$this->config->mail->mta}";
        if(!method_exists($this, $funcName)) $this->app->triggerError("The MTA {$this->config->mail->mta} not supported now.", __FILE__, __LINE__, $exit = true);
        $this->$funcName();
    }

    /**
     * Set smtp.
     * 
     * @access public
     * @return void
     */
    public function setSMTP()
    {
        $this->mta->isSMTP();
        $this->mta->SMTPDebug = $this->config->mail->smtp->debug;
        $this->mta->Host      = $this->config->mail->smtp->host;
        $this->mta->SMTPAuth  = $this->config->mail->smtp->auth;
        $this->mta->Username  = $this->config->mail->smtp->username;
        $this->mta->Password  = $this->config->mail->smtp->password;
        if(isset($this->config->mail->smtp->port)) $this->mta->Port = $this->config->mail->smtp->port;
        if(isset($this->config->mail->smtp->secure) and !empty($this->config->mail->smtp->secure))$this->mta->SMTPSecure = strtolower($this->config->mail->smtp->secure);
    }

    /**
     * PHPmail.
     * 
     * @access public
     * @return void
     */
    public function setPhpMail()
    {
        $this->mta->isMail();
    }

    /**
     * Sendmail.
     * 
     * @access public
     * @return void
     */
    public function setSendMail()
    {
        $this->mta->isSendmail();
    }

    /**
     * Gmail.
     * 
     * @access public
     * @return void
     */
    public function setGMail()
    {
        $this->mta->isSMTP();
        $this->mta->SMTPDebug  = $this->config->mail->gmail->debug;
        $this->mta->Host       = 'smtp.gmail.com';
        $this->mta->Port       = 465;
        $this->mta->SMTPSecure = "ssl";
        $this->mta->SMTPAuth   = true;
        $this->mta->Username   = $this->config->mail->gmail->username;
        $this->mta->Password   = $this->config->mail->gmail->password;
    }

    /**
     * Send email
     * 
     * @param  array   $toList 
     * @param  string  $subject 
     * @param  string  $body 
     * @param  string  $ccList 
     * @param  bool    $includeMe 
     * @param  string  $attachmentName
     * @param  mixed   $attachmentFile
     * @access public
     * @return void
     */
    public function send($toList, $subject, $body = '', $ccList = '', $includeMe = false, $attachmentName = '', $attachmentFile = '')
    {
        if(!$this->config->mail->turnon) return;

        ob_start();
        $toList  = $toList ? explode(',', str_replace(' ', '', $toList)) : array();
        $ccList  = $ccList ? explode(',', str_replace(' ', '', $ccList)) : array();

        /* Process toList and ccList, remove current user from them. If toList is empty, use the first cc as to. */
        if($includeMe == false)
        {
            $account = isset($this->app->user->account) ? $this->app->user->account : '';

            foreach($toList as $key => $to) if(trim($to) == $account or !trim($to)) unset($toList[$key]);
            foreach($ccList as $key => $cc) if(trim($cc) == $account or !trim($cc)) unset($ccList[$key]);
        }

        /* Remove deleted users. */
        $users = $this->loadModel('user')->getPairs('nodeleted,noforbidden');
        foreach($toList as $key => $to) if(!isset($users[trim($to)]) and strpos($to, '@') === false) unset($toList[$key]);
        foreach($ccList as $key => $cc) if(!isset($users[trim($cc)]) and strpos($to, '@') === false) unset($ccList[$key]);

        if(!$toList and !$ccList) return;
        if(!$toList and $ccList) $toList = array(array_shift($ccList));
        $toList = join(',', $toList);
        $ccList = join(',', $ccList);

        /* Get realname and email of users. */
        $this->loadModel('user');
        $emails = $this->user->getRealNameAndEmails(str_replace(' ', '', $toList . ',' . $ccList));
        
        $this->clear();

        /* Replace full webPath image for mail. */
        $sysURL      = commonModel::getSysURL();
        $readLinkReg = str_replace(array('%fileID%', '/', '.', '?'), array('[0-9]+', '\/', '\.', '\?'), helper::createLink('file', 'read', 'fileID=(%fileID%)', '\w+'));

        $body = preg_replace('/ src="(' . $readLinkReg . ')" /', ' src="' . $sysURL . '$1" ', $body);
        $body = preg_replace('/ src="{([0-9]+)(\.(\w+))?}" /', ' src="' . $sysURL . helper::createLink('file', 'read', "fileID=$1", "$3") . '" ', $body);
        $body = preg_replace('/<img (.*)src="\/?data\/upload/', '<img $1 src="' . $sysURL . $this->config->webRoot . 'data/upload', $body);

        try 
        {
            $this->mta->setFrom($this->config->mail->fromAddress, $this->convertCharset($this->config->mail->fromName));
            $this->setSubject($this->convertCharset($subject));
            $this->setTO($toList, $emails);
            $this->setCC($ccList, $emails);
            $this->setBody($this->convertCharset($body));
            if($attachmentFile) 
            {
                if(is_array($attachmentFile))
                {
                    foreach($attachmentFile as $file) 
                    {
                        if(isset($file->realpath)) $this->mta->AddAttachment($file->realpath, $file->title);
                    }
                }
                else
                {
                    $this->mta->AddAttachment($attachmentFile, $attachmentName);
                }
            }
            $this->setErrorLang();
            $this->mta->send();
        }
        catch (phpmailerException $e) 
        {
            $this->errors[] = nl2br(trim(strip_tags($e->errorMessage()))) . '<br />' . ob_get_contents();
        } 
        catch (Exception $e) 
        {
            $this->errors[] = trim(strip_tags($e->getMessage()));
        }

        /* save errors. */
        if($this->isError()) $this->app->saveError('E_MAIL', join(' ', $this->errors), __FILE__, __LINE__, true);

        $message = ob_get_contents();
        ob_clean();

        return $message;
    }

    /**
     * Set to address
     * 
     * @param  array    $toList 
     * @param  array    $emails 
     * @access public
     * @return void
     */
    public function setTO($toList, $emails)
    {
        $toList = explode(',', str_replace(' ', '', $toList));
        foreach($toList as $key => $account)
        {
            if(strpos($account, '@') !== false)
            {
                $realname = substr($account, 0, strpos($account, '@'));
                $emails[$account] = new stdClass();
                $emails[$account]->email    = $account;
                $emails[$account]->realname = $realname;
            }
            else if(!isset($emails[$account]) or isset($emails[$account]->sended) or strpos($emails[$account]->email, '@') == false)
            {
                continue;
            }
            $this->mta->addAddress($emails[$account]->email, $emails[$account]->realname);
            $emails[$account]->sended = true;
        }
    }

    /**
     * Set subject 
     * 
     * @param  string    $subject 
     * @access public
     * @return void
     */
    public function setSubject($subject)
    {
        $this->mta->Subject = stripslashes($subject);
    }

    /**
     * Set body.
     * 
     * @param  string    $body 
     * @access public
     * @return void
     */
    public function setBody($body)
    {
        $this->mta->msgHtml("$body");
    }

    /**
     * Set error lang. 
     * 
     * @access public
     * @return void
     */
    public function setErrorLang()
    {
        $this->mta->SetLanguage($this->app->getClientLang());
    }
   
    /**
     * Clear.
     * 
     * @access public
     * @return void
     */
    public function clear()
    {
        $this->mta->clearAddresses();
        $this->mta->clearAttachments();
    }

    /**
     * Check system if there is a mail at least.
     * 
     * @access public
     * @return bool | object 
     */
    public function mailExist()
    {
        return $this->dao->select('email')->from(TABLE_USER)->where('email')->ne('')->fetch();
    }

    /**
     * Is error?
     * 
     * @access public
     * @return bool
     */
    public function isError()
    {
        return !empty($this->errors);
    }

    /**
     * Get errors. 
     * 
     * @access public
     * @return void
     */
    public function getError()
    {
        $errors = $this->errors;
        $this->errors = array();
        return $errors;
    }

    /**
     * Convert charset.
     * 
     * @param  string    $string 
     * @access public
     * @return string
     */
    public function convertCharset($string)
    {
        if($this->config->mail->smtp->charset != strtolower($this->config->charset)) return iconv($this->config->charset, $this->config->mail->smtp->charset . '//IGNORE', $string);
        return $string;
    }

    /**
     * Set cc.
     * 
     * @param  array    $ccList 
     * @param  array    $emails 
     * @access public
     * @return void
     */
    public function setCC($ccList, $emails)
    {
        $ccList = explode(',', str_replace(' ', '', $ccList));
        if(!is_array($ccList)) return;
        foreach($ccList as $account)
        {
            if(!isset($emails[$account]) or isset($emails[$account]->sended) or strpos($emails[$account]->email, '@') == false) continue;
            $this->mta->addCC($emails[$account]->email, $this->convertCharset($emails[$account]->realname));
            $emails[$account]->sended = true;
        }
    }
}
