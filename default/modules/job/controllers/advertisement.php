<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Advertisement extends Employer_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->template->append_css('employer.css');
	}
	
	public function index()
	{
		$this->load->model(array('job/job_m','employer/employer_m'));			
		$employer_id = $this->employer_m->get_by(array('user_id'=>$this->current_user->id))->id;
		
		$jobs = $this->job_m->where('employer',$employer_id)->get_all();		
		
		$this->template
			->set('jobs',$jobs)
			->set('tp',lang('title_job_adv'))
			->build('advertisement/index');
	}
	
	public function create()
	{
		$this->load->model(array('job/career_level_m','job/qualification_m','job/industry_m','job/job_function_m','job/location_m',
			'job/employment_term_m'));
		
		$validation = array(			
			array(
				'field' => 'job_title',
				'label' => lang('label_job_title'),
				'rules'	=> 'required|trim|xss_clear'
			),
			array(
				'field' => 'job_description',
				'label' => lang('label_job_description'),
				'rules'	=> 'required|trim|xss_clear'
			),
			array(
				'field' => 'job_requirement',
				'label' => lang('label_job_requirement'),
				'rules'	=> 'required|trim|xss_clear'
			),
			array(
				'field' => 'career_level',
				'label' => lang('label_career_level'),
				'rules'	=> 'required|trim|xss_clear'
			),
			array(
				'field' => 'industry',
				'label' => lang('label_industry'),
				'rules'	=> 'required|trim|xss_clear'
			),
			array(
				'field' => 'job_function',
				'label' => lang('label_job_function'),
				'rules'	=> 'required|trim|xss_clear'
			),
			array(
				'field' => 'employment_type',
				'label' => lang('label_emloyment_term'),
				'rules'	=> 'required|trim|xss_clear'
			),
			array(
				'field' => 'salary_ket',
				'label' => lang('label_salary'),
				'rules'	=> 'required|trim|xss_clear'
			)
		);
		
		if ($this->input->post('btnSubmitJobAdv'))
		{			
			$this->form_validation->set_rules($validation);
			
			if ($this->form_validation->run())
			{
				$this->load->model('employer/employer_m');
				$employer_id = $this->employer_m->get_by(array('user_id'=>$this->current_user->id))->id;
				
				$range_sal_1 = 0;$range_sal_2 = 0;
				if ($this->input->post('salary_range_1'))
				{
					$range_sal_1 = $this->input->post('salary_range_1');
				}
				if ($this->input->post('salary_range_1'))
				{
					$range_sal_2 = $this->input->post('salary_range_2');
				}				
				$this->load->model('job/job_m');
				$result = $this->job_m->insert(array(
					'job_title'	=> $this->input->post('job_title'),
					'employer'	=> $employer_id,
					'career_level'	=> $this->input->post('career_level'),
					'year_of_exp'	=> $this->input->post('year_of_exp'),
					'qualification'	=> $this->input->post('qualification'),
					'industry'	=> $this->input->post('industry'),
					'employment_term'	=> $this->input->post('employment_type'),
					'date_posting'	=> date('Y-m-d', strtotime(date('Y-m-d'))),
					'date_closing'	=> date('Y-m-d', strtotime($this->input->post('date_closing'))),
					'job_requirement'	=> $this->input->post('job_requirement'),
					'job_description'	=> $this->input->post('job_description'),
					'job_function'	=> $this->input->post('job_function'),
					'location'	=> $this->input->post('location'),
					'salary_ket'	=> $this->input->post('salary_ket'),
					'salary_range_1'	=> $this->input->post('salary_range_1'),
					'salary_range_2'	=> $this->input->post('salary_range_2'),
				));
				
				if ($result)
				{					
					$this->template->success_string = lang('label_save_successful');
					$this->index();
					return;
				}
				else
				{
					$this->template->error_string = lang('label_save_unsuccessful');
				}
			}
			else 
			{
				$this->template->error_string = $this->form_validation->error_string();
			}
		}
		
		$career_levels = $this->career_level_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('key','name');
		$qualifications = $this->qualification_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('key','name');
		$industries = $this->industry_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('value','name');
		$job_functions = $this->job_function_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('value','name');
		$locations = $this->location_m->order_by('name')->dropdown('id','name');
		$employment_terms = $this->employment_term_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('key','name');
		
		$this->template
			->title(lang('label_employer'),lang('title_job_adv'))
			->append_css('datepicker.css')
			->append_js('bootstrap-datepicker.js')			
			->set('career_levels',$career_levels)
			->set('qualifications',$qualifications)
			->set('industries',$industries)
			->set('job_functions',$job_functions)
			->set('locations',$locations)
			->set('employment_terms',$employment_terms)
			->set('tp',lang('title_job_adv_create'))
			->build('advertisement/form');
	}

	public function edit($JobID=NULL)
	{
		$this->load->model(array('job/career_level_m',
								'job/qualification_m',
								'job/industry_m',
								'job/job_function_m',
								'job/location_m',
								'employer/employer_m',
								'job/employment_term_m',
								'job/job_m'));
		
		$employer_id = $this->employer_m->get_by(array('user_id'=>$this->current_user->id))->id;
		$jobad		 = $this->job_m->get_by(array('id'=>$JobID,'employer'=>$employer_id));
		
		if($jobad==NULL)
		{
			$this->session->set_flashdata('success', lang('label_edit_advertisement_wrong_id'));
			redirect('job/advertisement');
		}
		
		$validation = array(
						array(
							'field' => 'job_title',
							'label' => lang('label_job_title'),
							'rules'	=> 'required|trim|xss_clear'
						),
						array(
							'field' => 'job_description',
							'label' => lang('label_job_description'),
							'rules'	=> 'required|trim|xss_clear'
						),
						array(
							'field' => 'job_requirement',
							'label' => lang('label_job_requirement'),
							'rules'	=> 'required|trim|xss_clear'
						),
						array(
							'field' => 'career_level',
							'label' => lang('label_career_level'),
							'rules'	=> 'required|trim|xss_clear'
						),
						array(
							'field' => 'industry',
							'label' => lang('label_industry'),
							'rules'	=> 'required|trim|xss_clear'
						),
						array(
							'field' => 'job_function',
							'label' => lang('label_job_function'),
							'rules'	=> 'required|trim|xss_clear'
						),
						array(
							'field' => 'employment_type',
							'label' => lang('label_emloyment_term'),
							'rules'	=> 'required|trim|xss_clear'
						),
						array(
							'field' => 'salary_ket',
							'label' => lang('label_salary'),
							'rules'	=> 'required|trim|xss_clear'
						)
						);
		
		if ($this->input->post('btnSubmitJobAdv'))
		{
			$this->form_validation->set_rules($validation);
				
			if ($this->form_validation->run())
			{		
				$range_sal_1 = 0;$range_sal_2 = 0;
				if ($this->input->post('salary_range_1'))
				{
					$range_sal_1 = $this->input->post('salary_range_1');
				}
				if ($this->input->post('salary_range_1'))
				{
					$range_sal_2 = $this->input->post('salary_range_2');
				}
				$this->load->model('job/job_m');
				$result = $this->job_m
					->update(
						$this->input->post('id'),
						array(
							'job_title'			=> $this->input->post('job_title'),
							'employer'			=> $employer_id,
							'career_level'		=> $this->input->post('career_level'),
							'year_of_exp'		=> $this->input->post('year_of_exp'),
							'qualification'		=> $this->input->post('qualification'),
							'industry'			=> $this->input->post('industry'),
							'employment_term'	=> $this->input->post('employment_type'),
// 							'date_posting'		=> date('Y-m-d', strtotime(date('Y-m-d'))),
							'date_closing'		=> date('Y-m-d', strtotime($this->input->post('date_closing'))),
							'job_requirement'	=> $this->input->post('job_requirement'),
							'job_description'	=> $this->input->post('job_description'),
							'job_function'		=> $this->input->post('job_function'),
							'location'			=> $this->input->post('location'),
							'salary_ket'		=> $this->input->post('salary_ket'),
							'salary_range_1'	=> $this->input->post('salary_range_1'),
							'salary_range_2'	=> $this->input->post('salary_range_2')
					));
		
				if ($result)
				{
					$this->template->success_string = lang('label_save_successful');
					$this->index();
					return;
				}
				else
				{
					$this->template->error_string = lang('label_save_unsuccessful');
				}
			}
			else
			{
				$this->template->error_string = $this->form_validation->error_string();
			}
		}
		
		$career_levels = $this->career_level_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('key','name');
		$qualifications = $this->qualification_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('key','name');
		$industries = $this->industry_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('value','name');
		$job_functions = $this->job_function_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('value','name');
		$locations = $this->location_m->order_by('name')->dropdown('id','name');
		$employment_terms = $this->employment_term_m->where('lang',$_SESSION['lang_code'])->order_by('name')->dropdown('key','name');

		$this->template
			->title(lang('label_employer'),lang('title_job_adv'))
			->append_css('datepicker.css')
			->append_js('bootstrap-datepicker.js')
			->set('career_levels',$career_levels)
			->set('qualifications',$qualifications)
			->set('industries',$industries)
			->set('job_functions',$job_functions)
			->set('locations',$locations)
			->set('employment_terms',$employment_terms)
			->set('tp',lang('title_job_adv_edit'))
			->set('edit', TRUE)
    		->set('jobad', $jobad)
			->build('advertisement/form');
	}

	public function candidate($JobID=NULL)
	{
		$this->load->model(array('job/job_m','employer/employer_m','jobseeker/jobseeker_m','job/career_level_m','job/qualification_m'));
		$employer_id = $this->employer_m->get_by(array('user_id'=>$this->current_user->id))->id;
		
		$jobad			= $this->job_m->get_by(array('id'=>$JobID,'employer'=>$employer_id));
		$candidates		= $this->jobseeker_m->get_all();
		$career_level	= $this->career_level_m->where('lang',$_SESSION['lang_code'])->get_by(array('id' => $jobad->career_level))->name;
		$qualification	= $this->qualification_m->where('lang',$_SESSION['lang_code'])->get_by(array('id' => $jobad->qualification))->name;
		
		$this->template
			 ->set('jobad',$jobad)
			 ->set('career_level', $career_level)
			 ->set('qualification', $qualification)
			 ->set('tp',lang('title_job_adv_recommend'))
			 ->set('candidates', $candidates)
			 ->build('advertisement/candidate');
	}
	
	public function candidatedetail($CandidateID=NULL)
	{
		$this->load->model(array('job/job_m','employer/employer_m','jobseeker/jobseeker_m','job/career_level_m','job/qualification_m'));
		$employer_id = $this->employer_m->get_by(array('user_id'=>$this->current_user->id))->id;
		
		$jobad			= $this->job_m->get_by(array('id'=>$JobID,'employer'=>$employer_id));
		$candidates		= $this->jobseeker_m->get_all();
		$career_level	= $this->career_level_m->where('lang',$_SESSION['lang_code'])->get_by(array('id' => $jobad->career_level))->name;
		$qualification	= $this->qualification_m->where('lang',$_SESSION['lang_code'])->get_by(array('id' => $jobad->qualification))->name;
		
		$this->template
			->set('jobad',$jobad)
			->set('career_level', $career_level)
			->set('qualification', $qualification)
			->set('tp',lang('title_job_adv_recommend'))
			->set('candidates', $candidates)
			->build('advertisement/candidate');
	}
}