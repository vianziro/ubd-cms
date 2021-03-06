<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->_init();

		$this->load->model('auth/model_default');
		$this->load->helper('captcha');
		$this->load->library('form_validation');
	}

	private function _init()
	{
		$this->output->set_template('_default');
	}

	public function index()
	{
		if ($this->session->userdata('logged_in')) 
		{
			redirect(base_url('ubd-admin/dashboard'));
		} 
		else 
		{
			$this->login();
		}
	}

	public function login()
	{
		$data = array(
			'captcha' => $this->create_captcha(), 
		);

		$this->load->view('auth/index', $data, FALSE);
	}

	public function check_login()
	{
		$this->form_validation->set_rules('username', 'Username', 'trim|required', array('required' => '%s tidak boleh kosong'));
		$this->form_validation->set_rules('password', 'Password', 'trim|required', array('required' => '%s tidak boleh kosong'));
		$this->form_validation->set_rules('captcha', 'Captcha', 'trim|required', array('required' => '%s tidak boleh kosong'));

		if ($this->check_captcha($this->input->post('captcha')) == FALSE) 
		{
			$authResponse = array(
				'ResponMesage' => 'Captcha yang anda masukan salah atau captcha tidak boleh kosong',
				'ResponColor' => 'danger',
				'ResponTitle' => 'Gagal login!',
				'ResponCode' => 0);

			$_SESSION['ResponColor']  = $authResponse['ResponColor'];
			$_SESSION['ResponTitle']  = $authResponse['ResponTitle'];
			$_SESSION['ResponMesage']  = $authResponse['ResponMesage'];
			$this->session->mark_as_flash(array('ResponMesage', 'ResponColor', 'ResponTitle'));
			redirect(base_url('ubd-admin'));
		}
		else if ($this->form_validation->run() == FALSE) 
		{
			$authResponse = array(
				'ResponMesage' => validation_errors(),
				'ResponColor' => 'danger',
				'ResponTitle' => 'Gagal login!',
				'ResponCode' => 0);

			$_SESSION['ResponColor']  = $authResponse['ResponColor'];
			$_SESSION['ResponTitle']  = $authResponse['ResponTitle'];
			$_SESSION['ResponMesage']  = $authResponse['ResponMesage'];
			$this->session->mark_as_flash(array('ResponMesage', 'ResponColor', 'ResponTitle'));
			redirect(base_url('ubd-admin'));
		} 
		else 
		{
			$authResponse = $this->model_default->get_login_info($this->input->post('username'), $this->input->post('password'));

			if ($authResponse['row'] < 1) 
			{
				$_SESSION['ResponColor']  = 'danger';
				$_SESSION['ResponTitle']  = 'Gagal login!';
				$_SESSION['ResponMesage'] = 'Username dan Password salah, atau Status anda Tidak Aktif';
				$this->session->mark_as_flash(array('ResponMesage', 'ResponColor', 'ResponTitle'));
				redirect(base_url('ubd-admin'));
			} 
			else 
			{
				$sessionData = array(
					'logged_in' => TRUE,
					'user_id' => $authResponse['data']->user_id,
					'username' => $authResponse['data']->username,
					'nama_lengkap' => $authResponse['data']->nama_lengkap,
					'email' => $authResponse['data']->email,
					'user_picture' => $authResponse['data']->user_picture,
					'group' => $authResponse['data']->group,
					'user_status' => $authResponse['data']->user_status,
					'group_level' => $authResponse['data']->group_level,
					'group_name' => $authResponse['data']->group_name,
					'role' => $authResponse['data']->role,
					'registrasi_date' => $authResponse['data']->registrasi_date,
				);

				$this->session->set_userdata($sessionData);
				redirect(base_url('ubd-admin/dashboard'));
			}
		}
	}

	public function create_captcha()
	{
		$vals = array(
			//'word'          => 'Random word',
			'img_path'      => './ubd-content/captcha/',
			'img_url'       => base_url('ubd-content/captcha/'),
			//'font_path'     => './path/to/fonts/texb.ttf',
			'img_width'     => '165',
			'img_height'    => 45,
			'expiration'    => 1800,
			'word_length'   => 6,
			'font_size'     => 100,
			'img_id'        => 'Imageid',
			'pool'          => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',

			// White background and border, black text and red grid
			'colors'        => array(
				'background' => array(255, 255, 255),
				'border' => array(255, 255, 255),
				'text' => array(0, 0, 0),
				'grid' => array(210,214,222)
			)
		);

		$cap = create_captcha($vals);
		$image = $cap['image'];
		
		$this->session->set_userdata('captchaword', $cap['word']);

		return  $image;
	}

	public function check_captcha($str)
	{
		if ($str == $this->session->userdata('captchaword')) 
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function logout()
	{
		$user_data = $this->session->all_userdata();
        foreach ($user_data as $key => $value) {
            if ($key != 'session_id' && $key != 'ip_address' && $key != 'user_agent' && $key != 'last_activity') {
                $this->session->unset_userdata($key);
            }
        }
	    $this->session->sess_destroy();
	    redirect(base_url('ubd-admin'));
	}

}

/* End of file Auth.php */
/* Location: .//C/xampp/htdocs/Project/ubd-cms/ubd-modules/ubd-admin/auth/controllers/Auth.php */