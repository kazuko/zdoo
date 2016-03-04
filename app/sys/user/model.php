<?php
/**
 * The model file of user module of RanZhi.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     user
 * @version     $Id$
 * @link        http://www.ranzhico.com
 */
?>
<?php
class userModel extends model
{
    /**
     * Get users List.
     *
     * @param  int|array $dept
     * @param  string    $query 
     * @param  string    $orderBy
     * @param  object    $pager
     * @access public
     * @return array 
     */
    public function getList($dept = 0, $query = '', $orderBy = 'id', $pager = null)
    {
        return $this->dao->select('*')->from(TABLE_USER)
            ->where('deleted')->eq('0')
            ->beginIF($query != '')
            ->andWhere('account', true)->like("%$query%")
            ->orWhere('realname')->like("%$query%")
            ->markRight()
            ->fi()
            ->beginIF($dept != 0)->andWhere('dept')->in($dept)->fi()
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll();
    }

    /**
     * Get the account=>relaname pairs.
     * 
     * @param  string    $params  admin|noempty
     * @param  int|array $dept
     * @access public
     * @return array
     */
    public function getPairs($params = '', $dept = 0)
    {
        $users = $this->dao->select('account, realname')->from(TABLE_USER) 
            ->where('1=1')
            ->beginIF(strpos($params, 'nodeleted') !== false)->andWhere('deleted')->eq('0')->fi()
            ->beginIF(strpos($params, 'noforbidden') !== false)->andWhere('locked')->eq('0000-00-00 00:00:00')->fi()
            ->beginIF(strpos($params, 'admin') !== false)->andWhere('admin')->ne('no')->fi()
            ->beginIF($dept != 0)->andWhere('dept')->in($dept)->fi()
            ->orderBy('id_asc')    
            ->fetchPairs();

        foreach($users as $account => $realname) if($realname == '') $users[$account] = $account; 

        /* Append empty users. */
        if(strpos($params, 'noempty') === false) $users = array('' => '') + $users;
        if(strpos($params, 'noclosed') === false) $users = $users + array('closed' => 'Closed');

        return $users;
    }

    /**
     * Print use select.
     * 
     * @param  int    $name 
     * @param  string $selectedItems 
     * @param  string $attrib 
     * @param  string $params 
     * @param  int    $dept 
     * @access public
     * @return void
     */
    public function printSelect($name, $selectedItems = '', $attrib = '', $params = '', $dept = 0)
    {
        $options = $this->getPairs($params, $dept);
        return html::select($name, $options, $selectedItems, $attrib);
    }

    /**
     * Get the basic info of some user.
     * 
     * @param mixed $users 
     * @access public
     * @return void
     */
    public function getBasicInfo($users)
    {
        $users = $this->dao->select('account, admin, realname, `join`, last, visits')->from(TABLE_USER)->where('account')->in($users)->fetchAll('account', false);
        if(!$users) return array();

        foreach($users as $account => $user)
        {
            $user->realname  = empty($user->realname) ? $account : $user->realname;
            $user->shortLast = substr($user->last, 5, -3);
            $user->shortJoin = substr($user->join, 5, -3);
        }

        return $users;
    }

    /**
     * Get user by his account.
     * 
     * @param mixed $account
     * @access public
     * @return object           the user.
     */
    public function getByAccount($account)
    {
        return $this->dao->select('*')->from(TABLE_USER)
            ->beginIF(validater::checkEmail($account))->where('email')->eq($account)->fi()
            ->beginIF(!validater::checkEmail($account))->where('account')->eq($account)->fi()
            ->andWhere('deleted')->eq('0')
            ->fetch('', false);
    }

    /**
     * Get user list with email and real name.
     * 
     * @param  string|array $users 
     * @access public          
     * @return array           
     */
    public function getRealNameAndEmails($users)
    {
        $users = $this->dao->select('account, email, realname')->from(TABLE_USER)->where('account')->in($users)->fetchAll('account');
        if(!$users) return array();     
        foreach($users as $account => $user) if($user->realname == '') $user->realname = $account; 
        return $users;         
    }

    /**
     * Get user list with real name.
     * 
     * @param  string|array $users 
     * @access public          
     * @return array           
     */
    public function getRealNamePairs($users)
    {
        $userPairs = $this->dao->select('account, realname')->from(TABLE_USER)->where('account')->in($users)->fetchPairs('account');

        foreach($users as $account) if(!isset($userPairs[$account])) $userPairs[$account] = $account;

        if(!$userPairs) return array();     

        foreach($userPairs as $account => $realname) if($realname == '') $userPairs[$account] = $account; 

        return $userPairs;         
    }

    /**
     * Get roles for some users.
     * 
     * @param  string    $users 
     * @access public
     * @return array
     */
    public function getUserRoles($users)
    {
        return $this->dao->select('account, role')->from(TABLE_USER)->where('account')->in($users)->fetchPairs();
    }

    /**
     * Get role list.
     * 
     * @access public
     * @return void
     */
    public function getRoleList()
    {
        return $this->lang->user->roleList;
    }

    /**
     * Create a user.
     * 
     * @access public
     * @return void
     */
    public function create()
    {
        $this->checkPassword();

        $user = fixer::input('post')
            ->setForce('join', date('Y-m-d H:i:s'))
            ->setForce('last', helper::now())
            ->setForce('visits', 1)
            ->setIF($this->post->password1 == false, 'password', '')
            ->remove('admin, ip')
            ->get();
        $user->password = $this->createPassword($this->post->password1, $user->account); 

        $this->dao->insert(TABLE_USER)
            ->data($user, $skip = 'password1,password2')
            ->autoCheck()
            ->batchCheck($this->config->user->require->create, 'notempty')
            ->check('account', 'unique')
            ->check('account', 'account')
            ->check('email', 'email')
            ->check('email', 'unique')
            ->exec();
    }

    /**
     * Update an account.
     * 
     * @param  string $account 
     * @access public
     * @return void
     */
    public function update($account, $from)
    {
        /* If the user want to change his password. */
        if($this->post->password1 != false)
        {
            $this->checkPassword();
            if(dao::isError()) return false;

            $password  = $this->createPassword($this->post->password1, $account);
            $this->post->set('password', $password);
        }

        $user = fixer::input('post')
            ->cleanInt('imobile, qq, zipcode')
            ->remove('ip, account, join, visits')
            ->setIF($from == 'admin' and !$this->post->admin, 'admin', 'no');

        if($this->app->user->admin != 'super')
        {
            if($this->app->user->account != $account) return false;
            $user = $user->remove('admin');

            /* Remove check for role in front. */
            if($account == $this->app->user->account) $this->config->user->require->edit = str_replace(',role', '', $this->config->user->require->edit);
        }

        $user = $user->get();
        return $this->dao->update(TABLE_USER)
            ->data($user, $skip = 'password1,password2')
            ->autoCheck()
            ->batchCheck($this->config->user->require->edit, 'notempty')
            ->check('email', 'email')
            ->check('email', 'unique', "account!='$account'")
            ->checkIF($this->post->gtalk != false, 'gtalk', 'email')
            ->where('account')->eq($account)
            ->exec();
    }

    /**
     * Check the password is valid or not.
     * 
     * @access public
     * @return bool
     */
    public function checkPassword()
    {
        if($this->post->password1 != false)
        {
            if($this->post->password1 != $this->post->password2) dao::$errors['password1'][] = $this->lang->error->passwordsame;
            if(!validater::checkReg($this->post->password1, '|(.){6,}|')) dao::$errors['password1'][] = $this->lang->error->passwordrule;
        }
        else
        {
            dao::$errors['password1'][] = $this->lang->user->inputPassword;
        }
        return !dao::isError();
    }
    
    /**     
     * Update password 
     *          
     * @param  string $account 
     * @access public          
     * @return void
     */     
    public function updatePassword($account)
    { 
        $this->checkPassword();
        if(dao::isError()) return false;

        $user = fixer::input('post')
            ->setIF($this->post->password1 != false, 'password', $this->createPassword($this->post->password1, $account))
            ->remove('password1, password2, ip, account, admin, join, visits')
            ->get();

        $this->dao->update(TABLE_USER)->data($user)->autoCheck()->where('account')->eq($account)->exec();
    }   

    /**
     * Try to login with an account and password.
     * 
     * @param  string    $account 
     * @param  string    $password 
     * @access public
     * @return bool
     */
    public function login($account, $password)
    {
        $user = $this->identify($account, $password);
        if(!$user) return false;

        /* Set keep login cookie info if keep login. */
        if($this->post->keepLogin == 'true')
        {
            $this->keepLogin($user);
        }

        $user->password = $this->post->rawPassword;

        $user->rights = $this->authorize($user);
        $this->session->set('user', $user);
        $this->app->user = $this->session->user;

        $this->loadModel('action')->create('user', $user->id, 'login');

        return true;
    }

    /**
     * Identify a user.
     * 
     * @param   string $account     the account
     * @param   string $password    the password    the plain password or the md5 hash
     * @access  public
     * @return  object              if is valid user, return the user object.
     */
    public function identify($account, $password)
    {
        if(!$account or !$password) return false;

        /* First get the user from database by account or email. */
        $user = $this->dao->select('*')->from(TABLE_USER)
            ->where('deleted')->eq('0')
            ->beginIF(validater::checkEmail($account))->andWhere('email')->eq($account)->fi()
            ->beginIF(!validater::checkEmail($account))->andWhere('account')->eq($account)->fi()
            ->fetch();

        /* Then check the password hash. */
        if(!$user) return false;

        /* Can not login before ten minutes when user is locked. */
        if($user->locked != '0000-00-00 00:00:00')
        {
            $dateDiff = (strtotime($user->locked) - time()) / 60;

            /* Check the type of lock and show it. */
            if($dateDiff > 0 && $dateDiff <= 10)
            {
                $this->lang->user->loginFailed = sprintf($this->lang->user->locked, '10' . $this->lang->date->minute);
                return false;
            }
            elseif($dateDiff > 10)
            {
                $dateDiff = ceil($dateDiff / 60 / 24);
                $this->lang->user->loginFailed = $dateDiff <= 30 ? sprintf($this->lang->user->locked, $dateDiff . $this->lang->date->day) : $this->lang->user->lockedForEver;
                return false;
            }
            else
            {
                $user->fails  = 0;
                $user->locked = '0000-00-00 00:00:00';
            }
        }

        /* The password can be the plain or the password after md5. */
        if(!$this->compareHashPassword($password, $user))
        {
            $user->fails ++;
            if($user->fails > 2) $user->locked = date('Y-m-d H:i:s', time() + 10 * 60);
            $this->dao->update(TABLE_USER)->data($user)->where('id')->eq($user->id)->exec();
            return false;
        }

        /* Update user data. */
        $user->ip     = $this->server->remote_addr;
        $user->last   = helper::now();
        $user->ping   = helper::now();
        $user->fails  = 0;
        $user->visits ++;

        /* Update password when create password by oldCreatePassword function. */
        $this->dao->update(TABLE_USER)->data($user)->where('account')->eq($account)->exec();

        $user->realname  = empty($user->realname) ? $account : $user->realname;
        $user->shortLast = substr($user->last, 5, -3);
        $user->shortJoin = substr($user->join, 5, -3);
        unset($_SESSION['random']);

        /* Save sign in info. */
        if(commonModel::isAvailable('attend')) $this->loadModel('attend', 'oa')->signIn($user->account);

        /* Return him.*/
        return $user;
    }

    /**
     * Identify user by cookie.
     * 
     * @access public
     * @return void
     */
    public function identifyByCookie()
    {
        $account  = $this->cookie->ra;
        $authHash = $this->cookie->rp;
        $user     = $this->identify($account, $authHash);
        if(!$user) return false;
        
        /* Update keep login cookie info. */
        $this->keepLogin($user);

        $user->rights = $this->authorize($user);
        $this->session->set('user', $user);
        $this->app->user = $this->session->user;
    }

    /**
     * Authorize a user.
     * 
     * @param   object    $user   the user object.
     * @access  public
     * @return  array
     */
    public function authorize($user)
    {
        $rights = isset($this->config->rights->guest) ? $this->config->rights->guest : array();
        if($user->account == 'guest') return $rights;

        foreach($this->config->rights->member as $moduleName => $moduleMethods)
        {
            foreach($moduleMethods as $method) $rights[$moduleName][$method] = $method;
        }

        /* pull from ranzhi. */
        $sql = $this->dao->select('module, method')->from(TABLE_USERGROUP)->alias('t1')->leftJoin(TABLE_GROUPPRIV)->alias('t2')
            ->on('t1.group = t2.group')
            ->where('t1.account')->eq($user->account);
        $stmt = $sql->query();
        if(!$stmt) return $rights;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $rights[strtolower($row['module'])][strtolower($row['method'])] = true;
        }

        return $rights;
    }

    /**
     * Keep the user in login state.
     * 
     * @param  string    $account 
     * @param  string    $password 
     * @access public
     * @return void
     */
    public function keepLogin($user)
    {
        setcookie('keepLogin', 'on', $this->config->cookieLife, $this->config->webRoot);
        setcookie('ra', $user->account, $this->config->cookieLife, $this->config->webRoot);
        setcookie('rp', sha1($user->account . $user->password . $this->server->request_time), $this->config->cookieLife, $this->config->webRoot);
    }

    /**
     * Judge a user is logon or not.
     * 
     * @access public
     * @return bool
     */
    public function isLogon()
    {
        return (isset($_SESSION['user']) and !empty($_SESSION['user']) and $_SESSION['user']->account != 'guest');
    }

    /**
     * Judge a user is Online or not.
     * 
     * @param  string $account 
     * @access public
     * @return bool
     */
    public function isOnline($account)
    {
        $ping = $this->dao->select('ping')->from(TABLE_USER)->where('account')->eq($account)->fetch('ping');
        return (time() - strtotime($ping)) < 60;
    }

    /**
     * Record online status.
     * 
     * @access public
     * @return bool
     */
    public function online()
    {
        $this->dao->update(TABLE_USER)->set('ping')->eq(helper::now())->where('account')->eq($this->app->user->account)->exec();
        return true;
    }

    /**
     * Forbid the user
     *
     * @param int $userID
     * @access public
     * @return void
     */
    public function forbid($userID)
    {
        $this->dao->update(TABLE_USER)->set('locked')->eq('2038-01-19 00:00:00')->where('id')->eq($userID)->exec();
        return !dao::isError();
    }

    /**
     * Active user 
     * 
     * @param  int    $userID 
     * @access public
     * @return bool
     */
    public function active($userID)
    {
        $this->dao->update(TABLE_USER)->set('fails')->eq(0)->set('locked')->eq('0000-00-00 00:00:00')->where('id')->eq($userID)->exec();
        return !dao::isError();
    }

    /**
     * Delete user.
     * 
     * @param  string    $account 
     * @param  null      $id          add this param to avoid the warning of php.
     * @access public
     * @return bool
     */
    public function delete($account, $id = null) 
    {
        $user = $this->getByAccount($account);
        if(!$user) return false;

        parent::delete(TABLE_USER, $user->id);

        return !dao::isError();
    }

    /**
     * Create a strong password hash with md5.
     *
     * @param  string    $password 
     * @param  string    $account 
     * @access public
     * @return void
     */
    public function createPassword($password, $account)
    {
        return md5(md5($password) . $account);
    }

    /**
     * Compare hash password use random
     * 
     * @param  string    $password 
     * @param  object    $user 
     * @access public
     * @return void
     */
    public function compareHashPassword($password, $user)
    {
        /* Check Hash if password leng is 40. */
        $passwordLength = strlen($password);
        if($passwordLength == 40)
        {
            $hash = sha1($user->account . $user->password . strtotime($user->last));
            if($password == $hash) return true;
        }

        return $password == md5($user->password . $this->session->random);
    }

    /**
     * Upload avatar.
     * 
     * @access public
     * @return void
     */
    public function uploadAvatar()
    {
        $fileModel = $this->loadModel('file');
        $uploadResult = $fileModel->saveUpload('files');
        if(!$uploadResult) return array('result' => 'fail', 'message' => $this->lang->fail);

        $fileIdList = array_keys($uploadResult);
        $file = $this->file->getByID($fileIdList[0]);
        $this->dao->update(TABLE_USER)->set('avatar')->eq($file->fullURL)->where('account')->eq($this->app->user->account)->exec();
        return array('result' => 'success', 'message' => $this->lang->user->uploadSuccess, 'locate' => inlink('cropavatar', "image={$file->id}"));
    }
}
